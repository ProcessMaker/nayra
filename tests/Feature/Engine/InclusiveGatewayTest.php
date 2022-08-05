<?php

namespace Tests\Feature\Engine;

use ProcessMaker\Nayra\Bpmn\DefaultTransition;
use ProcessMaker\Nayra\Bpmn\Models\InclusiveGateway;
use ProcessMaker\Nayra\Contracts\Bpmn\ActivityInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\DataStoreInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\EndEventInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\EventInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\GatewayInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\InclusiveGatewayInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\ProcessInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\StartEventInterface;

/**
 * Test transitions
 */
class InclusiveGatewayTest extends EngineTestCase
{
    /**
     * Inclusive Gateway
     *           ┌─────────┐
     *        ┌─→│activityA│─┐
     *  ○─→╱╲─┘  └─────────┘ └─→╱╲
     *     ╲╱─┐  ┌─────────┐ ┌─→╲╱─→●
     *     A  └─→│activityB│─┘  B
     *           └─────────┘
     *
     * @return \ProcessMaker\Nayra\Contracts\Bpmn\ProcessInterface
     */
    private function createProcessWithInclusiveGateway()
    {
        $process = $this->repository->createProcess();

        //elements
        $start = $this->repository->createStartEvent();
        $gatewayA = $this->repository->createInclusiveGateway();
        $activityA = $this->repository->createActivity();
        $activityB = $this->repository->createActivity();
        $gatewayB = $this->repository->createInclusiveGateway();
        $end = $this->repository->createEndEvent();

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
        $start->createFlowTo($gatewayA, $this->repository);
        $gatewayA
            ->createConditionedFlowTo($activityA, function ($data) {
                return $data['A'] == '1';
            }, false, $this->repository)
            ->createConditionedFlowTo($activityB, function ($data) {
                return $data['B'] == '1';
            }, false, $this->repository);
        $activityA->createFlowTo($gatewayB, $this->repository);
        $activityB->createFlowTo($gatewayB, $this->repository);
        $gatewayB->createFlowTo($end, $this->repository);

        return $process;
    }

    /**
     * Creates a process that has a default transition
     *
     * @return \ProcessMaker\Models\Process
     */
    private function createProcessWithDefaultTransition()
    {
        $process = $this->repository->createProcess();

        //elements
        $start = $this->repository->createStartEvent();
        $gatewayA = $this->repository->createInclusiveGateway();
        $gatewayB = $this->repository->createInclusiveGateway();
        $gatewayA->name = 'A';
        $gatewayB->name = 'B';
        $activityA = $this->repository->createActivity();
        $activityB = $this->repository->createActivity();
        $end = $this->repository->createEndEvent();

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
        $start->createFlowTo($gatewayA, $this->repository);
        $gatewayA
            ->createConditionedFlowTo($activityA, function ($data) {
                return $data['A'] == '1';
            }, false, $this->repository)
            ->createConditionedFlowTo($activityB, function ($data) {
                return true;
            }, true, $this->repository);
        $activityA->createFlowTo($gatewayB, $this->repository);
        $activityB->createFlowTo($gatewayB, $this->repository);
        $gatewayB->createFlowTo($end, $this->repository);

        return $process;
    }

    /**
     * Test a inclusive gateway with two outgoing flows.
     *
     * Test transitions from start event, inclusive gateways, activities and end event,
     * with two activities activated.
     */
    public function testInclusiveGatewayAllPaths()
    {
        //Create a data store with data.
        $dataStore = $this->repository->createDataStore();
        $dataStore->putData('A', '1');
        $dataStore->putData('B', '1');

        //Load the process
        $process = $this->createProcessWithInclusiveGateway();
        $instance = $this->engine->createExecutionInstance($process, $dataStore);

        //Get References
        $start = $process->getEvents()->item(0);
        $activityA = $process->getActivities()->item(0);
        $activityB = $process->getActivities()->item(1);

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
            ActivityInterface::EVENT_ACTIVITY_ACTIVATED,
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
     * Test a inclusive gateway with one activated outgoing flow.
     *
     * Test transitions from start event, inclusive gateways, two activities and one end event,
     * with only one activity (B) activated.
     */
    public function testInclusiveGatewayOnlyB()
    {
        //Create a data store with data.
        $dataStore = $this->repository->createDataStore();
        $dataStore->putData('A', '0');
        $dataStore->putData('B', '1');
        $process = $this->createProcessWithInclusiveGateway();
        $instance = $this->engine->createExecutionInstance($process, $dataStore);

        //Get References
        $start = $process->getEvents()->item(0);
        $activityA = $process->getActivities()->item(0);
        $activityB = $process->getActivities()->item(1);

        //Start the process
        $start->start($instance);
        $this->engine->runToNextState();

        //Assertion: Verify the triggered engine events. One activity is activated.
        $this->assertEvents([
            ProcessInterface::EVENT_PROCESS_INSTANCE_CREATED,
            EventInterface::EVENT_EVENT_TRIGGERED,
            GatewayInterface::EVENT_GATEWAY_TOKEN_ARRIVES,
            GatewayInterface::EVENT_GATEWAY_ACTIVATED,
            GatewayInterface::EVENT_GATEWAY_TOKEN_CONSUMED,
            GatewayInterface::EVENT_GATEWAY_TOKEN_PASSED,
            ActivityInterface::EVENT_ACTIVITY_ACTIVATED,
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
            GatewayInterface::EVENT_GATEWAY_TOKEN_PASSED,
            EndEventInterface::EVENT_THROW_TOKEN_ARRIVES,
            EndEventInterface::EVENT_THROW_TOKEN_CONSUMED,
            EndEventInterface::EVENT_EVENT_TRIGGERED,
            ProcessInterface::EVENT_PROCESS_INSTANCE_COMPLETED,
        ]);
    }

    /**
     * Test that a process with a default transition is created and run correctly
     */
    public function testDefaultTransition()
    {
        //Create a data store with data.
        $dataStore = $this->repository->createDataStore();
        $dataStore->putData('A', '2');
        $process = $this->createProcessWithDefaultTransition();
        $instance = $this->engine->createExecutionInstance($process, $dataStore);

        //Get References
        $start = $process->getEvents()->item(0);

        //Start the process
        $start->start($instance);
        $this->engine->runToNextState();

        //Assertion: The correct events of the default transition should be triggered
        $this->assertEvents([
            ProcessInterface::EVENT_PROCESS_INSTANCE_CREATED,
            EventInterface::EVENT_EVENT_TRIGGERED,
            GatewayInterface::EVENT_GATEWAY_TOKEN_ARRIVES,
            GatewayInterface::EVENT_GATEWAY_ACTIVATED,
            GatewayInterface::EVENT_GATEWAY_TOKEN_CONSUMED,
            DefaultTransition::EVENT_CONDITIONED_TRANSITION,
            GatewayInterface::EVENT_GATEWAY_TOKEN_PASSED,
            ActivityInterface::EVENT_ACTIVITY_ACTIVATED,
        ]);

        $dataStore->putData('A', '1');
        $process = $this->createProcessWithDefaultTransition();
        $instance = $this->engine->createExecutionInstance($process, $dataStore);

        //Get References
        $start = $process->getEvents()->item(0);

        //Start the process
        $start->start($instance);
        $this->engine->runToNextState();
        //Assertion: The correct events of the default transition should be triggered
        $this->assertEvents([
            ProcessInterface::EVENT_PROCESS_INSTANCE_CREATED,
            EventInterface::EVENT_EVENT_TRIGGERED,
            GatewayInterface::EVENT_GATEWAY_TOKEN_ARRIVES,
            GatewayInterface::EVENT_GATEWAY_ACTIVATED,
            GatewayInterface::EVENT_GATEWAY_TOKEN_CONSUMED,
            GatewayInterface::EVENT_GATEWAY_TOKEN_PASSED,
            ActivityInterface::EVENT_ACTIVITY_ACTIVATED,
        ]);
    }
}
