<?php

namespace Tests\Feature\Engine;

use ProcessMaker\Nayra\Contracts\Bpmn\ActivityInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\EventNodeInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\GatewayInterface;

/**
 * Test transitions
 *
 */
class ExclusiveGatewayTest extends EngineTestCase
{

    /**
     * Creates a process where the exclusive gateway has conditioned and simple transitions
     *
     * @return \ProcessMaker\Models\Process
     */
    private function createProcessWithExclusiveGateway()
    {
        $process = $this->processRepository->createProcessInstance();

        //elements
        $start = $this->eventRepository->createStartEventInstance();
        $gatewayA = $this->gatewayRepository->createExclusiveGatewayInstance();
        $activityA = $this->activityRepository->createActivityInstance();
        $activityB = $this->activityRepository->createActivityInstance();
        $activityC = $this->activityRepository->createActivityInstance();

        $end = $this->eventRepository->createEndEventInstance();
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
        $start->createFlowTo($gatewayA, $this->flowRepository);
        $gatewayA
            ->createConditionedFlowTo($activityA, function ($data) {
                return $data['A']=='1';
            }, false, $this->flowRepository)
            ->createConditionedFlowTo($activityB, function ($data) {
                return $data['B']=='1';
            }, false, $this->flowRepository)
            ->createFlowTo($activityC, $this->flowRepository);
        $activityA->createFlowTo($end, $this->flowRepository);
        $activityB->createFlowTo($end, $this->flowRepository);
        $activityC->createFlowTo($end, $this->flowRepository);
        return $process;
    }

    /**
     * Creates a process with default transitions
     *
     * @return \ProcessMaker\Models\Process
     */
    private function createProcessWithExclusiveGatewayAndDefaultTransition()
    {
        $process = $this->processRepository->createProcessInstance();

        //elements
        $start = $this->eventRepository->createStartEventInstance();
        $gatewayA = $this->gatewayRepository->createExclusiveGatewayInstance();
        $activityA = $this->activityRepository->createActivityInstance();
        $activityB = $this->activityRepository->createActivityInstance();
        $activityC = $this->activityRepository->createActivityInstance();

        $end = $this->eventRepository->createEndEventInstance();
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
        $start->createFlowTo($gatewayA, $this->flowRepository);
        $gatewayA
            ->createConditionedFlowTo($activityA, function ($data) {
                return $data['A']=='1';
            }, false, $this->flowRepository)
            ->createConditionedFlowTo($activityB, function ($data) {
                return $data['B']=='1';
            }, false, $this->flowRepository)
            ->createConditionedFlowTo($activityC, function ($data) {
                return true;
            }, true, $this->flowRepository);
        $activityA->createFlowTo($end, $this->flowRepository);
        $activityB->createFlowTo($end, $this->flowRepository);
        $activityC->createFlowTo($end, $this->flowRepository);
        return $process;
    }

    /**
     * Parallel diverging Exclusive converging
     *           ┌─────────┐
     *        ┌─→│activityA│─┐
     *  ○─→╱╲─┘  └─────────┘ └─→╱╲  ┌─────────┐
     *     ╲╱─┐  ┌─────────┐ ┌─→╲╱─→│activityC│─→●
     *     A  └─→│activityB│─┘  B   └─────────┘
     *  parallel └─────────┘  exclusive
     *
     * @return \ProcessMaker\Nayra\Contracts\Bpmn\ProcessInterface
     */
    private function createParallelDivergingExclusiveConverging()
    {
        $process = $this->processRepository->createProcessInstance();

        //elements
        $start = $this->eventRepository->createStartEventInstance();
        $gatewayA = $this->gatewayRepository->createParallelGatewayInstance();
        $activityA = $this->activityRepository->createActivityInstance();
        $activityB = $this->activityRepository->createActivityInstance();
        $activityC = $this->activityRepository->createActivityInstance();

        $gatewayB = $this->gatewayRepository->createExclusiveGatewayInstance();
        $end = $this->eventRepository->createEndEventInstance();
        $process
            ->addActivity($activityA)
            ->addActivity($activityB)
            ->addActivity($activityC);
        $process
            ->addGateway($gatewayA)
            ->addGateway($gatewayB);
        $process
            ->addEvent($start)
            ->addEvent($end);

        //flows
        $start->createFlowTo($gatewayA, $this->flowRepository);
        $gatewayA
            ->createFlowTo($activityA, $this->flowRepository)
            ->createFlowTo($activityB, $this->flowRepository);
        $activityA->createFlowTo($gatewayB, $this->flowRepository);
        $activityB->createFlowTo($gatewayB, $this->flowRepository);
        $gatewayB->createFlowTo($activityC, $this->flowRepository);
        $activityC->createFlowTo($end, $this->flowRepository);
        return $process;
    }

    /**
     * Tests the basic functionality of the exclusive gateway
     */
    public function testExclusiveGateway()
    {
        //Create a data store with data.
        $dataStore = $this->dataStoreRepository->createDataStoreInstance();
        $dataStore->putData('A', '2');
        $dataStore->putData('B', '1');

        //Load the process
        $process = $this->createProcessWithExclusiveGateway();
        $this->engine->createExecutionInstance($process, $dataStore);

        //Get References
        $start = $process->getEvents()->item(0);
        $activityB = $process->getActivities()->item(1);

        //Start the process
        $start->start();

        $this->engine->runToNextState();

        //Assertion: Verify the triggered engine events. Two activities are activated.
        $this->assertEvents([
            EventNodeInterface::EVENT_EVENT_TRIGGERED,
            GatewayInterface::EVENT_GATEWAY_TOKEN_ARRIVES,
            GatewayInterface::EVENT_GATEWAY_ACTIVATED,
            GatewayInterface::EVENT_GATEWAY_TOKEN_CONSUMED,
            GatewayInterface::EVENT_GATEWAY_TOKEN_PASSED,
            ActivityInterface::EVENT_ACTIVITY_ACTIVATED,
        ]);

        $tokenB = $activityB->getTokens($dataStore)->item(0);
        $activityB->complete($tokenB);

        //the run to next state should go false when the max steps is reached.
        $this->assertFalse($this->engine->runToNextState(1));
    }

    /**
     * Tests that the correct events are triggered when the first flow has a condition evaluated to true
     */
    public function testExclusiveGatewayFirstConditionTrue()
    {
        //Create a data store with data.
        $dataStore = $this->dataStoreRepository->createDataStoreInstance();
        $dataStore->putData('A', '1');
        $dataStore->putData('B', '1');

        //Load the process
        $process = $this->createProcessWithExclusiveGatewayAndDefaultTransition();
        $this->engine->createExecutionInstance($process, $dataStore);

        //Get References
        $start = $process->getEvents()->item(0);

        //Start the process
        $start->start();

        $this->engine->runToNextState();

        //Assertion: Verify the triggered engine events. Two activities are activated.
        $this->assertEvents([
            EventNodeInterface::EVENT_EVENT_TRIGGERED,
            GatewayInterface::EVENT_GATEWAY_TOKEN_ARRIVES,
            GatewayInterface::EVENT_GATEWAY_ACTIVATED,
            GatewayInterface::EVENT_GATEWAY_TOKEN_CONSUMED,
            GatewayInterface::EVENT_GATEWAY_TOKEN_PASSED,
            ActivityInterface::EVENT_ACTIVITY_ACTIVATED,
        ]);
    }

    /**
     * Tests the exclusive gateway triggering the default transition
     */
    public function testExclusiveGatewayWithDefaultTransition()
    {
        //Create a data store with data.
        $dataStore = $this->dataStoreRepository->createDataStoreInstance();
        $dataStore->putData('A', '2');
        $dataStore->putData('B', '2');

        //Load the process
        $process = $this->createProcessWithExclusiveGatewayAndDefaultTransition();
        $this->engine->createExecutionInstance($process, $dataStore);

        //Get References
        $start = $process->getEvents()->item(0);
        $activityC = $process->getActivities()->item(2);

        //Start the process
        $start->start();

        $this->engine->runToNextState();

        //Assertion: Verify the triggered engine events. Two activities are activated.
        $this->assertEvents([
            EventNodeInterface::EVENT_EVENT_TRIGGERED,
            GatewayInterface::EVENT_GATEWAY_TOKEN_ARRIVES,
            GatewayInterface::EVENT_GATEWAY_ACTIVATED,
            GatewayInterface::EVENT_GATEWAY_TOKEN_CONSUMED,
            GatewayInterface::EVENT_GATEWAY_TOKEN_PASSED,
            ActivityInterface::EVENT_ACTIVITY_ACTIVATED,
        ]);

        //Completes the Activity C
        $tokenC = $activityC->getTokens($dataStore)->item(0);
        $activityC->complete($tokenC);

        //the run to next state should go false when the max steps is reached.
        $this->assertFalse($this->engine->runToNextState(1));

        $this->engine->runToNextState();

        //Assertion: Verify the triggered engine events. The activity is closed and process is ended.
        $this->assertEvents([
            ActivityInterface::EVENT_ACTIVITY_COMPLETED,
            ActivityInterface::EVENT_ACTIVITY_CLOSED,
            EventNodeInterface::EVENT_EVENT_TRIGGERED
        ]);
    }

    /**
     * Parallel diverging Exclusive converging
     *
     * A process with three tasks, a diverging parallelGateway and a converging exclusiveGateway.
     * Two of the tasks are executed in parallel and then merged by the exclusiveGateway.
     * As a result, the task following the exclusiveGateway should be followed twice.
     */
    public function testParallelDivergingExclusiveConverging()
    {
        //Create a data store with data.
        $dataStore = $this->dataStoreRepository->createDataStoreInstance();

        //Load the process
        $process = $this->createParallelDivergingExclusiveConverging();
        $this->engine->createExecutionInstance($process, $dataStore);

        //Get References
        $start = $process->getEvents()->item(0);
        $activityA = $process->getActivities()->item(0);
        $activityB = $process->getActivities()->item(1);
        $activityC = $process->getActivities()->item(2);

        //Start the process
        $start->start();

        $this->engine->runToNextState();

        //Assertion: Verify the triggered engine events. Two activities are activated.
        $this->assertEvents([
            EventNodeInterface::EVENT_EVENT_TRIGGERED,
            GatewayInterface::EVENT_GATEWAY_TOKEN_ARRIVES,
            GatewayInterface::EVENT_GATEWAY_ACTIVATED,
            GatewayInterface::EVENT_GATEWAY_TOKEN_CONSUMED,
            GatewayInterface::EVENT_GATEWAY_TOKEN_PASSED,
            ActivityInterface::EVENT_ACTIVITY_ACTIVATED,
            GatewayInterface::EVENT_GATEWAY_TOKEN_PASSED,
            ActivityInterface::EVENT_ACTIVITY_ACTIVATED
        ]);

        //Completes the Activity A
        $tokenA = $activityA->getTokens($dataStore)->item(0);
        $activityA->complete($tokenA);

        //the run to next state should go false when the max steps is reached.
        $this->assertFalse($this->engine->runToNextState(1));

        $this->engine->runToNextState();

        //Assertion: Verify the triggered engine events. The activity is closed.
        $this->assertEvents([
            ActivityInterface::EVENT_ACTIVITY_COMPLETED,
            ActivityInterface::EVENT_ACTIVITY_CLOSED,
            GatewayInterface::EVENT_GATEWAY_TOKEN_ARRIVES,
            GatewayInterface::EVENT_GATEWAY_ACTIVATED,
            GatewayInterface::EVENT_GATEWAY_TOKEN_CONSUMED,
            GatewayInterface::EVENT_GATEWAY_TOKEN_PASSED,
            ActivityInterface::EVENT_ACTIVITY_ACTIVATED,
        ]);

        //Completes the Activity B
        $tokenB = $activityB->getTokens($dataStore)->item(0);
        $activityB->complete($tokenB);
        $this->engine->runToNextState();

        //Assertion: Verify the triggered engine events. The activity B is closed.
        $this->assertEvents([
            ActivityInterface::EVENT_ACTIVITY_COMPLETED,
            ActivityInterface::EVENT_ACTIVITY_CLOSED,
            GatewayInterface::EVENT_GATEWAY_TOKEN_ARRIVES,
            GatewayInterface::EVENT_GATEWAY_ACTIVATED,
            GatewayInterface::EVENT_GATEWAY_TOKEN_CONSUMED,
            GatewayInterface::EVENT_GATEWAY_TOKEN_PASSED,
            ActivityInterface::EVENT_ACTIVITY_ACTIVATED,
        ]);

        //Assertion: ActivityC has two tokens.
        $this->assertEquals(2, $activityC->getTokens($dataStore)->count());

        //Completes the Activity C for the first token
        $tokenC = $activityC->getTokens($dataStore)->item(0);
        $activityC->complete($tokenC);
        $this->engine->runToNextState();

        //Completes the Activity C for the next token
        $tokenC = $activityC->getTokens($dataStore)->item(0);
        $activityC->complete($tokenC);
        $this->engine->runToNextState();

        //Assertion: ActivityC has no tokens.
        $this->assertEquals(0, $activityC->getTokens($dataStore)->count());

        //Assertion: ActivityC was completed and closed per each token, then the end event was triggered twice.
        $this->assertEvents([
            ActivityInterface::EVENT_ACTIVITY_COMPLETED,
            ActivityInterface::EVENT_ACTIVITY_CLOSED,
            EventNodeInterface::EVENT_EVENT_TRIGGERED,
            ActivityInterface::EVENT_ACTIVITY_COMPLETED,
            ActivityInterface::EVENT_ACTIVITY_CLOSED,
            EventNodeInterface::EVENT_EVENT_TRIGGERED,
        ]);
    }
}
