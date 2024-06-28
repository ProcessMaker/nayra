<?php

namespace Tests\Feature\Engine;

use ProcessMaker\Nayra\Contracts\Bpmn\ActivityInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\EndEventInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\EventInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\GatewayInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\ProcessInterface;

/**
 * Test transitions
 */
class ParallelGatewayDemoTest extends EngineTestCase
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
        $start = $this->repository->createStartEvent()->setId('start');
        $gatewayA = $this->repository->createParallelGateway()->setId('gatewayA');
        $activityA = $this->repository->createActivity()->setId('ActivityA');
        $activityB = $this->repository->createActivity()->setId('ActivityB');
        $activityC = $this->repository->createActivity()->setId('ActivityC');

        $gatewayB = $this->repository->createParallelGateway()->setId('gatewayB');
        $end = $this->repository->createEndEvent()->setId('end');
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
     */
    public function testParallelGateway()
    {
        //Create a data store with data.
        $dataStore = $this->repository->createDataStore();

        // Enable demo mode
        $this->engine->setDemoMode(true);

        //Load the process
        $process = $this->createProcessWithParallelGateway();
        $instance = $this->engine->createExecutionInstance($process, $dataStore);

        //Get References
        $start = $process->getEvents()->item(0);
        $activityA = $process->getActivities()->item(0);
        $activityB = $process->getActivities()->item(1);
        $activityC = $process->getActivities()->item(2);

        //Start the process
        $start->start($instance);

        $this->engine->runToNextState();

        //Assertion: Verify the triggered engine events. Two activities are activated.
        $this->assertEvents([
            ProcessInterface::EVENT_PROCESS_INSTANCE_CREATED,
            EventInterface::EVENT_EVENT_TRIGGERED,
            GatewayInterface::EVENT_GATEWAY_TOKEN_ARRIVES,
        ]);

        // Select the flow for the gateway and run the engine
        $gateway = $process->getGateways()->item(0);
        $selectedFlow = $gateway->getOutgoingFlows()->item(0);
        $this->engine->setSelectedDemoFlow($gateway, $selectedFlow);
        $this->engine->runToNextState();

        //Assertion: Verify the Activity A was activated
        $this->assertEvents([
            GatewayInterface::EVENT_GATEWAY_ACTIVATED,
            GatewayInterface::EVENT_GATEWAY_TOKEN_CONSUMED,
            GatewayInterface::EVENT_GATEWAY_TOKEN_PASSED,
            ActivityInterface::EVENT_ACTIVITY_ACTIVATED,
        ]);

        //Completes the Activity A
        $tokenA = $activityA->getTokens($instance)->item(0);
        $activityA->complete($tokenA);
        $this->engine->runToNextState();

        //Assertion: Verify the triggered engine events. The activity is closed.
        $this->assertEvents([
            ActivityInterface::EVENT_ACTIVITY_COMPLETED,
            ActivityInterface::EVENT_ACTIVITY_CLOSED,
            GatewayInterface::EVENT_GATEWAY_TOKEN_ARRIVES,
        ]);

        // Select the flow for the gateway B and run the engine
        $gateway = $process->getGateways()->item(1);
        $selectedFlow = $gateway->getOutgoingFlows()->item(0);
        $this->engine->setSelectedDemoFlow($gateway, $selectedFlow);
        $this->engine->runToNextState();

        //Assertion: Verify the triggered engine events. The activity is closed.
        $this->assertEvents([
            GatewayInterface::EVENT_GATEWAY_ACTIVATED,
            GatewayInterface::EVENT_GATEWAY_TOKEN_CONSUMED,
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
}
