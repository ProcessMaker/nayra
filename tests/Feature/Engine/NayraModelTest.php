<?php

namespace Tests\Feature\Engine;

use ProcessMaker\Nayra\Contracts\Bpmn\ActivityInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\DataStoreInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\EndEventInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\EventInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\ExclusiveGatewayInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\FlowInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\GatewayInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\InclusiveGatewayInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\ProcessInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\StartEventInterface;
use ProcessMaker\Nayra\Contracts\Factory;
use ProcessMaker\Nayra\Contracts\FactoryInterface;

class NayraModelTest extends EngineTestCase
{
    public function testProcessWithExclusiveGateway()
    {
        $config = $this->createMappingConfiguration();
        $factory = new Factory($config);

        $processData = $this->createProcessWithExclusiveGateway($factory);
        $process = $processData['process'];
        $start = $processData['start'];
        $activityA = $processData['activityA'];
        $activityB = $processData['activityB'];
        $activityC = $processData['activityC'];
        $dataStore = $processData['dataStore'];
        $dataStore->putData('A', '2');
        $dataStore->putData('B', '1');

        $instance = $this->engine->createExecutionInstance($process, $dataStore);

        //Start the process
        $start->start();

        $this->engine->runToNextState();

        //Assertion: Verify the triggered engine events. Two activities are activated.
        $this->assertEvents([
            EventInterface::EVENT_EVENT_TRIGGERED,
            GatewayInterface::EVENT_GATEWAY_TOKEN_ARRIVES,
            GatewayInterface::EVENT_GATEWAY_ACTIVATED,
            GatewayInterface::EVENT_GATEWAY_TOKEN_CONSUMED,
            GatewayInterface::EVENT_GATEWAY_TOKEN_PASSED,
            ActivityInterface::EVENT_ACTIVITY_ACTIVATED,
        ]);

        $tokenB = $activityB->getTokens($instance)->item(0);
        $activityB->complete($tokenB);

        //the run to next state should go false when the max steps is reached.
        $this->assertFalse($this->engine->runToNextState(1));
    }

    public function testProcessWithInclusiveGateway()
    {
        $config = $this->createMappingConfiguration();
        $factory = new Factory($config);

        $processData = $this->createProcessWithInclusiveGateway($factory);
        $process = $processData['process'];
        $start = $processData['start'];
        $activityA = $processData['activityA'];
        $activityB = $processData['activityB'];
        $dataStore = $processData['dataStore'];
        $dataStore->putData('A', '1');
        $dataStore->putData('B', '1');

        $instance = $this->engine->createExecutionInstance($process, $dataStore);

        //Start the process
        $start->start();

        $this->engine->runToNextState();

        //Assertion: Verify the triggered engine events. Two activities are activated.
        $this->assertEvents([
            EventInterface::EVENT_EVENT_TRIGGERED,
            GatewayInterface::EVENT_GATEWAY_TOKEN_ARRIVES,
            GatewayInterface::EVENT_GATEWAY_ACTIVATED,
            GatewayInterface::EVENT_GATEWAY_TOKEN_CONSUMED,
            GatewayInterface::EVENT_GATEWAY_TOKEN_PASSED,
            ActivityInterface::EVENT_ACTIVITY_ACTIVATED,
            GatewayInterface::EVENT_GATEWAY_TOKEN_PASSED,
            ActivityInterface::EVENT_ACTIVITY_ACTIVATED
        ]);

        //Completes the Activity A
        $tokenA = $activityA->getTokens($instance)->item(0);
        $activityA->complete($tokenA);

        //the run to next state should go false when the max steps is reached.
        $this->assertFalse($this->engine->runToNextState(1));

        $this->engine->runToNextState();

        //Assertion: Verify the triggered engine events. The activity is closed.
        $this->assertEvents([
            ActivityInterface::EVENT_ACTIVITY_COMPLETED,
            ActivityInterface::EVENT_ACTIVITY_CLOSED,
            GatewayInterface::EVENT_GATEWAY_TOKEN_ARRIVES,
        ]);

        //Completes the Activity B
        $tokenB = $activityB->getTokens($instance)->item(0);
        $activityB->complete($tokenB);
        $this->engine->runToNextState();

        //Assertion: Verify the triggered engine events. The activity is closed and process is ended.
        $this->assertEvents([
            ActivityInterface::EVENT_ACTIVITY_COMPLETED,
            ActivityInterface::EVENT_ACTIVITY_CLOSED,
            GatewayInterface::EVENT_GATEWAY_TOKEN_ARRIVES,
            GatewayInterface::EVENT_GATEWAY_ACTIVATED,
            GatewayInterface::EVENT_GATEWAY_TOKEN_CONSUMED,
            GatewayInterface::EVENT_GATEWAY_TOKEN_CONSUMED,
            GatewayInterface::EVENT_GATEWAY_TOKEN_PASSED,
            EndEventInterface::EVENT_THROW_TOKEN_ARRIVES,
            EndEventInterface::EVENT_THROW_TOKEN_CONSUMED,
            EndEventInterface::EVENT_EVENT_TRIGGERED,
            ProcessInterface::EVENT_PROCESS_COMPLETED,
        ]);
    }

    private function createMappingConfiguration()
    {
        return [
            ActivityInterface::class => \ProcessMaker\Nayra\Model\Activity::class,
            StartEventInterface::class => \ProcessMaker\Nayra\Model\StartEvent::class,
            EndEventInterface::class => \ProcessMaker\Nayra\Model\EndEvent::class,
            ExclusiveGatewayInterface::class => \ProcessMaker\Nayra\Model\ExclusiveGateway::class,
            InclusiveGatewayInterface::class => \ProcessMaker\Nayra\Model\InclusiveGateway::class,
            ProcessInterface::class => \ProcessMaker\Models\Process::class,
            DataStoreInterface::class => \ProcessMaker\Models\DataStore::class,
            FlowInterface::class => \ProcessMaker\Models\Flow::class,
        ];
    }

    private function createProcessWithExclusiveGateway(FactoryInterface $factory)
    {
        $process = $factory->getInstanceOf(ProcessInterface::class);
        $start = $factory->getInstanceOf(StartEventInterface::class);
        $gatewayA = $factory->getInstanceOf(ExclusiveGatewayInterface::class);
        $activityA = $factory->getInstanceOf(ActivityInterface::class);
        $activityB = $factory->getInstanceOf(ActivityInterface::class);
        $activityC = $factory->getInstanceOf(ActivityInterface::class);
        $end = $factory->getInstanceOf(EndEventInterface::class);
        $dataStore = $factory->getInstanceOf(DataStoreInterface::class);

        $process
            ->addActivity($activityA)
            ->addActivity($activityB)
            ->addActivity($activityC);

        $process
            ->addGateway($gatewayA);

        $process
            ->addEvent($start)
            ->addEvent($end);

        //flows
        $start->createFlowTo($gatewayA, $factory);
        $gatewayA
            ->createConditionedFlowTo($activityA, function ($data) {
                return $data['A']=='1';
            }, false, $factory)
            ->createConditionedFlowTo($activityB, function ($data) {
                return $data['B']=='1';
            }, false, $factory)
            ->createFlowTo($activityC, $factory);

        $activityA->createFlowTo($end, $factory);
        $activityB->createFlowTo($end, $factory);
        $activityC->createFlowTo($end, $factory);

        return [
            'process' => $process,
            'start' => $start,
            'gatewayA' => $gatewayA,
            'activityA' => $activityA,
            'activityB' => $activityB,
            'activityC' => $activityC,
            'end' => $end,
            'dataStore' => $dataStore,
        ];
    }

    private function createProcessWithInclusiveGateway($factory)
    {
        $process = $factory->getInstanceOf(ProcessInterface::class);
        $start = $factory->getInstanceOf(StartEventInterface::class);
        $gatewayA = $factory->getInstanceOf(InclusiveGatewayInterface::class);
        $gatewayB = $factory->getInstanceOf(InclusiveGatewayInterface::class);
        $activityA = $factory->getInstanceOf(ActivityInterface::class);
        $activityB = $factory->getInstanceOf(ActivityInterface::class);
        $end = $factory->getInstanceOf(EndEventInterface::class);
        $dataStore = $factory->getInstanceOf(DataStoreInterface::class);

        $process
            ->addActivity($activityA)
            ->addActivity($activityB);
        $process
            ->addGateway($gatewayA)
            ->addGateway($gatewayB);
        $process
            ->addEvent($start)
            ->addEvent($end);

        //flows
        $start->createFlowTo($gatewayA, $factory);
        $gatewayA
            ->createConditionedFlowTo($activityA, function ($data) {
                return $data['A'] == '1';
            }, false, $factory)
            ->createConditionedFlowTo($activityB, function ($data) {
                return $data['B'] == '1';
            }, false, $factory);
        $activityA->createFlowTo($gatewayB, $factory);
        $activityB->createFlowTo($gatewayB, $factory);
        $gatewayB->createFlowTo($end, $factory);
        return [
            'process' => $process,
            'start' => $start,
            'gatewayA' => $gatewayA,
            'gatewayB' => $gatewayB,
            'activityA' => $activityA,
            'activityB' => $activityB,
            'end' => $end,
            'dataStore' => $dataStore,
        ];
    }
}
