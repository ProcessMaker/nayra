<?php

namespace Tests\Feature\Engine;

use ProcessMaker\Nayra\Contracts\Bpmn\ActivityInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\DataStoreInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\EndEventInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\EventInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\ExclusiveGatewayInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\GatewayInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\InclusiveGatewayInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\ProcessInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\StartEventInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\TransitionInterface;
use ProcessMaker\Nayra\Contracts\RepositoryInterface;

/**
 * Tests for the Nayra/Bpmn/Model classes
 *
 * @package Tests\Feature\Engine
 */
class NayraModelTest extends EngineTestCase
{
    /**
     * Tests a process with exclusive gateways that uses the Nayra/Bpmn/Model classes
     */
    public function testProcessWithExclusiveGateway()
    {
        $processData = $this->createProcessWithExclusiveGateway($this->repository);
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
        $start->start($instance);

        $this->engine->runToNextState();

        //Assertion: Verify the triggered engine events. Two activities are activated.
        $this->assertEvents([
            ProcessInterface::EVENT_PROCESS_INSTANCE_CREATED,
            EventInterface::EVENT_EVENT_TRIGGERED,
            GatewayInterface::EVENT_GATEWAY_TOKEN_ARRIVES,
            GatewayInterface::EVENT_GATEWAY_ACTIVATED,
            GatewayInterface::EVENT_GATEWAY_TOKEN_CONSUMED,
            TransitionInterface::EVENT_CONDITIONED_TRANSITION,
            GatewayInterface::EVENT_GATEWAY_TOKEN_PASSED,
            ActivityInterface::EVENT_ACTIVITY_ACTIVATED,
        ]);

        $tokenB = $activityB->getTokens($instance)->item(0);
        $activityB->complete($tokenB);

        //the run to next state should go false when the max steps is reached.
        $this->assertFalse($this->engine->runToNextState(1));
    }

    /**
     * Tests a process with inclusive gateways that uses the Nayra/Bpmn/Model classes
     */
    public function testProcessWithInclusiveGateway()
    {
        $processData = $this->createProcessWithInclusiveGateway($this->repository);
        $process = $processData['process'];
        $start = $processData['start'];
        $activityA = $processData['activityA'];
        $activityB = $processData['activityB'];
        $dataStore = $processData['dataStore'];
        $dataStore->putData('A', '1');
        $dataStore->putData('B', '1');

        $instance = $this->engine->createExecutionInstance($process, $dataStore);

        //Start the process
        $start->start($instance);

        $this->engine->runToNextState();

        //Assertion: Verify the triggered engine events. Two activities are activated.
        $this->assertEvents([
            ProcessInterface::EVENT_PROCESS_INSTANCE_CREATED,
            EventInterface::EVENT_EVENT_TRIGGERED,
            GatewayInterface::EVENT_GATEWAY_TOKEN_ARRIVES,
            GatewayInterface::EVENT_GATEWAY_ACTIVATED,
            GatewayInterface::EVENT_GATEWAY_TOKEN_CONSUMED,
            GatewayInterface::EVENT_GATEWAY_TOKEN_PASSED,
            GatewayInterface::EVENT_GATEWAY_TOKEN_PASSED,
            ActivityInterface::EVENT_ACTIVITY_ACTIVATED,
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
            ProcessInterface::EVENT_PROCESS_INSTANCE_COMPLETED,
        ]);
    }

    /**
     * Creates a process that contains an exclusive gateway, start, end events and activities
     *
     * @param RepositoryInterface $factory
     *
     * @return array
     */
    private function createProcessWithExclusiveGateway(RepositoryInterface $factory)
    {
        $process = $factory->createProcess();
        $start = $factory->createStartEvent();
        $gatewayA = $factory->createExclusiveGateway();
        $activityA = $factory->createActivity();
        $activityB = $factory->createActivity();
        $activityC = $factory->createActivity();
        $end = $factory->createEndEvent();
        $dataStore = $factory->createDataStore();

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

    /**
     * Creates a process that contains an inclusive gateway, start, end events and activities
     *
     * @param FactoryInterface $factory
     *
     * @return array
     */
    private function createProcessWithInclusiveGateway($factory)
    {
        $process = $factory->createProcess();
        $start = $factory->createStartEvent();
        $gatewayA = $factory->createInclusiveGateway();
        $gatewayB = $factory->createInclusiveGateway();
        $activityA = $factory->createActivity();
        $activityB = $factory->createActivity();
        $end = $factory->createEndEvent();
        $dataStore = $factory->createDataStore();

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
