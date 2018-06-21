<?php

namespace Tests\Feature\Engine;

use ProcessMaker\Nayra\Contracts\Bpmn\ActivityInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\DataStoreInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\EndEventInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\EventInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\GatewayInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\InclusiveGatewayInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\ParallelGatewayInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\ProcessInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\StartEventInterface;

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
        $process = $this->repository->createProcess();

        //elements
        $start = $this->repository->createStartEvent();
        $gatewayA = $this->repository->createParallelGateway();
        $activityA = $this->repository->createActivity();
        $activityB = $this->repository->createActivity();
        $activityC = $this->repository->createActivity();

        $gatewayB = $this->repository->createParallelGateway();
        $end =  $this->repository->createEndEvent();
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
        $start->createFlowTo($gatewayA, $this->repository);
        $gatewayA
            ->createFlowTo($activityA, $this->repository)
            ->createFlowTo($activityB, $this->repository);
        $activityA->createFlowTo($gatewayB, $this->repository);
        $activityB->createFlowTo($gatewayB, $this->repository);
        $gatewayB->createFlowTo($activityC, $this->repository);
        $activityC->createFlowTo($end, $this->repository);
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
        $process = $this->repository->createProcess();

        //elements
        $start = $this->repository->createStartEvent();
        $gatewayA = $this->repository->createParallelGateway();
        $activityA = $this->repository->createActivity();
        $activityB = $this->repository->createActivity();
        $activityC = $this->repository->createActivity();

        $gatewayB = $this->repository->createInclusiveGateway();
        $end = $this->repository->createEndEvent();

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
        $start->createFlowTo($gatewayA, $this->repository);
        $gatewayA
            ->createFlowTo($activityA, $this->repository)
            ->createFlowTo($activityB, $this->repository);
        $activityA->createFlowTo($gatewayB, $this->repository);
        $activityB->createFlowTo($gatewayB, $this->repository);
        $gatewayB->createFlowTo($activityC, $this->repository);
        $activityC->createFlowTo($end, $this->repository);
        return $process;
    }

    /**
     * Test a parallel gateway with two outgoing flows.
     *
     */
    public function testParallelGateway()
    {
        //Create a data store with data.
        $dataStore =  $this->repository->createDataStore();

        //Load the process
        $process = $this->createProcessWithParallelGateway();
        $instance = $this->engine->createExecutionInstance($process, $dataStore);

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
            GatewayInterface::EVENT_GATEWAY_TOKEN_ARRIVES,
        ]);

        //Completes the Activity B
        $tokenB = $activityB->getTokens($instance)->item(0);
        $activityB->complete($tokenB);
        $this->engine->runToNextState();

        //Assertion: Verify the triggered engine events. The activity B is closed and process is ended.
        $this->assertEvents([
            ActivityInterface::EVENT_ACTIVITY_COMPLETED,
            GatewayInterface::EVENT_GATEWAY_TOKEN_ARRIVES,
            GatewayInterface::EVENT_GATEWAY_ACTIVATED,
            GatewayInterface::EVENT_GATEWAY_TOKEN_CONSUMED,
            ActivityInterface::EVENT_ACTIVITY_CLOSED,
            GatewayInterface::EVENT_GATEWAY_TOKEN_CONSUMED,
            ActivityInterface::EVENT_ACTIVITY_CLOSED,
            GatewayInterface::EVENT_GATEWAY_TOKEN_PASSED,
            ActivityInterface::EVENT_ACTIVITY_ACTIVATED,
        ]);

        //Assertion: ActivityC has one token.
        $this->assertEquals(1, $activityC->getTokens($instance)->count());

        //Completes the Activity C
        $tokenC = $activityC->getTokens($instance)->item(0);
        $activityC->complete($tokenC);
        $this->engine->runToNextState();

        //Assertion: ActivityC has no tokens.
        $this->assertEquals(0, $activityC->getTokens($instance)->count());

        //Assertion: ActivityC was completed and closed, then the process has ended.
        $this->assertEvents([
            ActivityInterface::EVENT_ACTIVITY_COMPLETED,
            ActivityInterface::EVENT_ACTIVITY_CLOSED,
            EndEventInterface::EVENT_THROW_TOKEN_ARRIVES,
            EndEventInterface::EVENT_THROW_TOKEN_CONSUMED,
            EventInterface::EVENT_EVENT_TRIGGERED,
            ProcessInterface::EVENT_PROCESS_INSTANCE_COMPLETED,
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
        $dataStore =  $this->repository->createDataStore();

        //Load the process
        $process = $this->createParallelDivergingInclusiveConverging();
        $instance = $this->engine->createExecutionInstance($process, $dataStore);

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
            GatewayInterface::EVENT_GATEWAY_TOKEN_ARRIVES,
        ]);

        //Completes the Activity B
        $tokenB = $activityB->getTokens($instance)->item(0);
        $activityB->complete($tokenB);
        $this->engine->runToNextState();

        //Assertion: Verify the triggered engine events. The activity B is closed.
        $this->assertEvents([
            ActivityInterface::EVENT_ACTIVITY_COMPLETED,
            GatewayInterface::EVENT_GATEWAY_TOKEN_ARRIVES,
            GatewayInterface::EVENT_GATEWAY_ACTIVATED,
            GatewayInterface::EVENT_GATEWAY_TOKEN_CONSUMED,
            ActivityInterface::EVENT_ACTIVITY_CLOSED,
            GatewayInterface::EVENT_GATEWAY_TOKEN_CONSUMED,
            ActivityInterface::EVENT_ACTIVITY_CLOSED,
            GatewayInterface::EVENT_GATEWAY_TOKEN_PASSED,
            ActivityInterface::EVENT_ACTIVITY_ACTIVATED,
        ]);

        //Assertion: ActivityC has one token.
        $this->assertEquals(1, $activityC->getTokens($instance)->count());

        //Completes the Activity C
        $tokenC = $activityC->getTokens($instance)->item(0);
        $activityC->complete($tokenC);
        $this->engine->runToNextState();

        //Assertion: ActivityC has no tokens.
        $this->assertEquals(0, $activityC->getTokens($instance)->count());

        //Assertion: ActivityC was completed and closed, then the process has ended.
        $this->assertEvents([
            ActivityInterface::EVENT_ACTIVITY_COMPLETED,
            ActivityInterface::EVENT_ACTIVITY_CLOSED,
            EndEventInterface::EVENT_THROW_TOKEN_ARRIVES,
            EndEventInterface::EVENT_THROW_TOKEN_CONSUMED,
            EventInterface::EVENT_EVENT_TRIGGERED,
            ProcessInterface::EVENT_PROCESS_INSTANCE_COMPLETED,
        ]);
    }

    /**
     * Test parallel gateway can not have conditioned outgoing flows.
     *
     */
    public function testParallelCanNotHaveConditionedOutgoingFlow()
    {
        //Create a parallel gateway and an activity.
        $gatewayA =  $this->repository->createParallelGateway();
        $activityA =  $this->repository->createActivity();

        //Assertion: Throw exception when creating a conditioned flow from parallel.
        $this->expectException('ProcessMaker\Nayra\Exceptions\InvalidSequenceFlowException');
        $gatewayA->createConditionedFlowTo($activityA, function() {}, false, $this->repository);
        $process =  $this->repository->createProcess();
        $process
            ->addActivity($activityA)
            ->addGateway($gatewayA);
    }
}
