<?php

namespace Tests\Feature\Engine;

use PHPUnit\Framework\TestCase;
use ProcessMaker\Nayra\Contracts\Bpmn\ActivityInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\EventNodeInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\GatewayInterface;
use ProcessMaker\Nayra\Contracts\Engine\EngineInterface;
use ProcessMaker\Bpmn\TestEngine;
use ProcessMaker\Models\RepositoryFactory;

/**
 * Test transitions
 *
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
        $process = $this->processRepository->createProcessInstance();

        //elements
        $start = $this->eventRepository->createStartEventInstance();
        $gatewayA = $this->gatewayRepository->createInclusiveGatewayInstance();
        $activityA = $this->activityRepository->createActivityInstance();
        $activityB = $this->activityRepository->createActivityInstance();

        $gatewayB = $this->gatewayRepository->createInclusiveGatewayInstance();
        $end = $this->eventRepository->createEndEventInstance();
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
        $start->createFlowTo($gatewayA, $this->flowRepository);
        $gatewayA
            ->createConditionedFlowTo($activityA, function ($data) {
                return $data['A']=='1';
            }, false, $this->flowRepository)
            ->createConditionedFlowTo($activityB, function ($data) {
                return $data['B']=='1';
            }, false, $this->flowRepository);
        $activityA->createFlowTo($gatewayB, $this->flowRepository);
        $activityB->createFlowTo($gatewayB, $this->flowRepository);
        $gatewayB->createFlowTo($end, $this->flowRepository);
        return $process;
    }

    /**
     * Creates a process that has a default transition
     *
     * @return \ProcessMaker\Models\Process
     */
    private function createProcessWithDefaultTransition()
    {
        $process = $this->processRepository->createProcessInstance();

        //elements
        $start = $this->eventRepository->createStartEventInstance();
        $gatewayA = $this->gatewayRepository->createInclusiveGatewayInstance();
        $gatewayB = $this->gatewayRepository->createInclusiveGatewayInstance();
        $gatewayA->name= "A";
        $gatewayB->name= "B";
        $activityA = $this->activityRepository->createActivityInstance();
        $activityB = $this->activityRepository->createActivityInstance();

        $end = $this->eventRepository->createEndEventInstance();
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
        $start->createFlowTo($gatewayA, $this->flowRepository);
        $gatewayA
            ->createConditionedFlowTo($activityA, function ($data) {
                return $data['A']=='1';
            }, false, $this->flowRepository)
            ->createConditionedFlowTo($activityB, function ($data) {
                return true;
            }, true, $this->flowRepository);
        $activityA->createFlowTo($gatewayB, $this->flowRepository);
        $activityB->createFlowTo($gatewayB, $this->flowRepository);
        $gatewayB->createFlowTo($end, $this->flowRepository);
        return $process;
    }

    /**
     * Test transitions from start event, inclusive gateways, activities and end event,
     * with both activities activated.
     */
    public function testInclusiveGatewayAllPaths()
    {
        //Data store to access the runtime data.
        $dataStore = $this->dataStoreRepository->createDataStoreInstance();
        $dataStore->putData('A', '1');
        $dataStore->putData('B', '1');
        $process = $this->createProcessWithInclusiveGateway();
        $this->engine->createExecutionInstance($process, $dataStore);

        //Get References
        $start = $process->getEvents()->item(0);
        $activityA = $process->getActivities()->item(0);
        $activityB = $process->getActivities()->item(1);

        //Start the process
        $start->start();
        $this->engine->runToNextState();
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
        $this->engine->runToNextState();
        $this->assertEvents([
            ActivityInterface::EVENT_ACTIVITY_COMPLETED,
            ActivityInterface::EVENT_ACTIVITY_CLOSED,
            GatewayInterface::EVENT_GATEWAY_TOKEN_ARRIVES,
        ]);

        //Completes the Activity B
        $tokenB = $activityB->getTokens($dataStore)->item(0);
        $activityB->complete($tokenB);
        $this->engine->runToNextState();
        $this->assertEvents([
            ActivityInterface::EVENT_ACTIVITY_COMPLETED,
            ActivityInterface::EVENT_ACTIVITY_CLOSED,
            GatewayInterface::EVENT_GATEWAY_TOKEN_ARRIVES,
            GatewayInterface::EVENT_GATEWAY_ACTIVATED,
            GatewayInterface::EVENT_GATEWAY_TOKEN_CONSUMED,
            GatewayInterface::EVENT_GATEWAY_TOKEN_CONSUMED,
            GatewayInterface::EVENT_GATEWAY_TOKEN_PASSED,
            EventNodeInterface::EVENT_EVENT_TRIGGERED
        ]);
    }

    /**
     * Test transitions from start event, inclusive gateways, activities and end event,
     * with only activity B activated.
     */
    public function testInclusiveGatewayOnlyB()
    {
        //Data store to access the runtime data.
        $dataStore = $this->dataStoreRepository->createDataStoreInstance();
        $dataStore->putData('A', '0');
        $dataStore->putData('B', '1');
        $process = $this->createProcessWithInclusiveGateway();
        $this->engine->createExecutionInstance($process, $dataStore);

        //Get References
        $start = $process->getEvents()->item(0);
        $activityA = $process->getActivities()->item(0);
        $activityB = $process->getActivities()->item(1);

        //Start the process
        $start->start();
        $this->engine->runToNextState();
        $this->assertEvents([
            EventNodeInterface::EVENT_EVENT_TRIGGERED,
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
        $this->assertEvents([
            ActivityInterface::EVENT_ACTIVITY_COMPLETED,
            ActivityInterface::EVENT_ACTIVITY_CLOSED,
            GatewayInterface::EVENT_GATEWAY_TOKEN_ARRIVES,
            GatewayInterface::EVENT_GATEWAY_ACTIVATED,
            GatewayInterface::EVENT_GATEWAY_TOKEN_CONSUMED,
            GatewayInterface::EVENT_GATEWAY_TOKEN_PASSED,
            EventNodeInterface::EVENT_EVENT_TRIGGERED,
        ]);
    }

    /**
     * Test that a process with a default transition is created and run correctly
     */
    public function testDefaultTransition()
    {
        //Data store to access the runtime data.
        $dataStore = $this->dataStoreRepository->createDataStoreInstance();
        $dataStore->putData('A', '2');
        $process = $this->createProcessWithDefaultTransition();
        $this->engine->createExecutionInstance($process, $dataStore);

        //Get References
        $start = $process->getEvents()->item(0);

        //Start the process
        $start->start();
        $this->engine->runToNextState();

        //The correct events of the default transition should be triggered
        $this->assertEvents([
            EventNodeInterface::EVENT_EVENT_TRIGGERED,
            GatewayInterface::EVENT_GATEWAY_TOKEN_ARRIVES,
            GatewayInterface::EVENT_GATEWAY_ACTIVATED,
            GatewayInterface::EVENT_GATEWAY_TOKEN_CONSUMED,
            GatewayInterface::EVENT_GATEWAY_TOKEN_PASSED,
            ActivityInterface::EVENT_ACTIVITY_ACTIVATED
        ]);
    }
}
