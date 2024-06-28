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
class InclusiveGatewayDemoTest extends EngineTestCase
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
        $gatewayA = $this->repository->createInclusiveGateway()->setId('GatewayA');
        $activityA = $this->repository->createActivity()->setName('Activity A');
        $activityB = $this->repository->createActivity()->setName('Activity B');
        $gatewayB = $this->repository->createInclusiveGateway()->setId('GatewayB');
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
        $activityA = $this->repository->createActivity()->setName('Activity A');
        $activityB = $this->repository->createActivity()->setName('Activity B');
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

        // Enable demo mode
        $this->engine->setDemoMode(true);

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
            // GatewayInterface::EVENT_GATEWAY_ACTIVATED,
            // GatewayInterface::EVENT_GATEWAY_TOKEN_CONSUMED,
            // GatewayInterface::EVENT_GATEWAY_TOKEN_PASSED,
            // GatewayInterface::EVENT_GATEWAY_TOKEN_PASSED,
            // ActivityInterface::EVENT_ACTIVITY_ACTIVATED,
            // ActivityInterface::EVENT_ACTIVITY_ACTIVATED,
        ]);

        // Select the flow for the gateway and run the engine
        $gateway = $process->getGateways()->item(0);
        $selectedFlow = $gateway->getOutgoingFlows()->item(0);
        $this->engine->setSelectedDemoFlow($gateway, $selectedFlow);
        $this->engine->runToNextState();

        // Assertion: Engine runs to the next state because a flow was selected manually
        $this->assertEvents([
            GatewayInterface::EVENT_GATEWAY_ACTIVATED,
            GatewayInterface::EVENT_GATEWAY_TOKEN_CONSUMED,
            GatewayInterface::EVENT_GATEWAY_TOKEN_PASSED,
            ActivityInterface::EVENT_ACTIVITY_ACTIVATED,
        ]);

        //Only one activity is activated because only one flow was selected manually
        $this->assertCount(1, $activityA->getTokens($instance));

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

        //Assertion: Verify the triggered engine events. The activity is closed and process is ended.
        $this->assertEvents([
            GatewayInterface::EVENT_GATEWAY_ACTIVATED,
            GatewayInterface::EVENT_GATEWAY_TOKEN_CONSUMED,
            GatewayInterface::EVENT_GATEWAY_TOKEN_PASSED,
            EndEventInterface::EVENT_THROW_TOKEN_ARRIVES,
            EndEventInterface::EVENT_THROW_TOKEN_CONSUMED,
            EndEventInterface::EVENT_EVENT_TRIGGERED,
            ProcessInterface::EVENT_PROCESS_INSTANCE_COMPLETED,
        ]);
    }
}
