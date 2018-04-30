<?php

namespace Tests\Feature\Engine;

use ProcessMaker\Nayra\Contracts\Bpmn\ActivityInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\EventInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\GatewayInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\ProcessInterface;

/**
 * Test transitions
 *
 */
class ParallelGatewayTest extends EngineTestCase
{

    /**
     * Parallel Gateway
     *           ┌─────────┐
     *        ┌─→│activityA│─┐
     *  ○─→╱╲─┘  └─────────┘ └─→╱╲  ┌─────────┐
     *     ╲╱─┐  ┌─────────┐ ┌─→╲╱─→│activityC│─→●
     *     A  └─→│activityB│─┘  B   └─────────┘
     *           └─────────┘
     *
     * @return \ProcessMaker\Nayra\Contracts\Bpmn\ProcessInterface
     */
    private function createProcessWithParallelGateway()
    {
        $process = $this->processRepository->createProcessInstance();

        //elements
        $start = $this->eventRepository->createStartEventInstance();
        $gatewayA = $this->gatewayRepository->createParallelGatewayInstance();
        $activityA = $this->activityRepository->createActivityInstance();
        $activityB = $this->activityRepository->createActivityInstance();
        $activityC = $this->activityRepository->createActivityInstance();

        $gatewayB = $this->gatewayRepository->createParallelGatewayInstance();
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
     * Parallel Diverging Inclusive converging
     *           ┌─────────┐
     *        ┌─→│activityA│─┐
     *  ○─→╱╲─┘  └─────────┘ └─→╱╲  ┌─────────┐
     *     ╲╱─┐  ┌─────────┐ ┌─→╲╱─→│activityC│─→●
     *     A  └─→│activityB│─┘  B   └─────────┘
     * Parallel  └─────────┘  Inclusive
     *
     * @return \ProcessMaker\Nayra\Contracts\Bpmn\ProcessInterface
     */
    private function createParallelDivergingInclusiveConverging()
    {
        $process = $this->processRepository->createProcessInstance();

        //elements
        $start = $this->eventRepository->createStartEventInstance();
        $gatewayA = $this->gatewayRepository->createParallelGatewayInstance();
        $activityA = $this->activityRepository->createActivityInstance();
        $activityB = $this->activityRepository->createActivityInstance();
        $activityC = $this->activityRepository->createActivityInstance();

        $gatewayB = $this->gatewayRepository->createInclusiveGatewayInstance();
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
     * Test a parallel gateway with two outgoing flows.
     *
     */
    public function testParallelGateway()
    {
        //Create a data store with data.
        $dataStore = $this->dataStoreRepository->createDataStoreInstance();

        //Load the process
        $process = $this->createProcessWithParallelGateway();
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
        ]);

        //Completes the Activity B
        $tokenB = $activityB->getTokens($dataStore)->item(0);
        $activityB->complete($tokenB);
        $this->engine->runToNextState();

        //Assertion: Verify the triggered engine events. The activity B is closed and process is ended.
        $this->assertEvents([
            ActivityInterface::EVENT_ACTIVITY_COMPLETED,
            ActivityInterface::EVENT_ACTIVITY_CLOSED,
            GatewayInterface::EVENT_GATEWAY_TOKEN_ARRIVES,
            GatewayInterface::EVENT_GATEWAY_ACTIVATED,
            GatewayInterface::EVENT_GATEWAY_TOKEN_CONSUMED,
            GatewayInterface::EVENT_GATEWAY_TOKEN_CONSUMED,
            GatewayInterface::EVENT_GATEWAY_TOKEN_PASSED,
            ActivityInterface::EVENT_ACTIVITY_ACTIVATED,
        ]);

        //Assertion: ActivityC has one token.
        $this->assertEquals(1, $activityC->getTokens($dataStore)->count());

        //Completes the Activity C
        $tokenC = $activityC->getTokens($dataStore)->item(0);
        $activityC->complete($tokenC);
        $this->engine->runToNextState();

        //Assertion: ActivityC has no tokens.
        $this->assertEquals(0, $activityC->getTokens($dataStore)->count());

        //Assertion: ActivityC was completed and closed, then the process has ended.
        $this->assertEvents([
            ActivityInterface::EVENT_ACTIVITY_COMPLETED,
            ActivityInterface::EVENT_ACTIVITY_CLOSED,
            EventInterface::EVENT_EVENT_TRIGGERED,
            ProcessInterface::EVENT_PROCESS_COMPLETED,
        ]);
    }

    /**
     * Test parallel diverging then inclusive converging.
     *
     * Two of the tasks are executed in parallel and merged by the inclusiveGateway.
     */
    public function testParallelDivergingInclusiveConverging()
    {
        //Create a data store with data.
        $dataStore = $this->dataStoreRepository->createDataStoreInstance();

        //Load the process
        $process = $this->createParallelDivergingInclusiveConverging();
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
            GatewayInterface::EVENT_GATEWAY_TOKEN_CONSUMED,
            GatewayInterface::EVENT_GATEWAY_TOKEN_PASSED,
            ActivityInterface::EVENT_ACTIVITY_ACTIVATED,
        ]);

        //Assertion: ActivityC has one token.
        $this->assertEquals(1, $activityC->getTokens($dataStore)->count());

        //Completes the Activity C
        $tokenC = $activityC->getTokens($dataStore)->item(0);
        $activityC->complete($tokenC);
        $this->engine->runToNextState();

        //Assertion: ActivityC has no tokens.
        $this->assertEquals(0, $activityC->getTokens($dataStore)->count());

        //Assertion: ActivityC was completed and closed, then the process has ended.
        $this->assertEvents([
            ActivityInterface::EVENT_ACTIVITY_COMPLETED,
            ActivityInterface::EVENT_ACTIVITY_CLOSED,
            EventInterface::EVENT_EVENT_TRIGGERED,
            ProcessInterface::EVENT_PROCESS_COMPLETED,
        ]);
    }

    /**
     * Test parallel gateway can not have conditioned outgoing flows.
     *
     */
    public function testParallelCanNotHaveConditionedOutgoingFlow()
    {
        //Create a parallel gateway and an activity.
        $gatewayA = $this->gatewayRepository->createParallelGatewayInstance();
        $activityA = $this->activityRepository->createActivityInstance();

        //Assertion: Throw exception when creating a conditioned flow from parallel.
        $this->expectException('ProcessMaker\Nayra\Exceptions\InvalidSequenceFlowException');
        $gatewayA->createConditionedFlowTo($activityA, function() {}, false, $this->flowRepository);
        $process = $this->processRepository->createProcessInstance();
        $process
            ->addActivity($activityA)
            ->addGateway($gatewayA);
    }
}
