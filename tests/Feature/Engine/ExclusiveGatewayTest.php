<?php

namespace Tests\Feature\Engine;

use ProcessMaker\Nayra\Bpmn\DefaultTransition;
use ProcessMaker\Nayra\Contracts\Bpmn\ActivityInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\DataStoreInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\EndEventInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\EventInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\ExclusiveGatewayInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\GatewayInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\ParallelGatewayInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\ProcessInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\StartEventInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\TransitionInterface;
use ProcessMaker\Nayra\Storage\BpmnDocument;

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
        $process = $this->repository->createProcess();

        //elements
        $start = $this->repository->createStartEvent();
        $gatewayA = $this->repository->createExclusiveGateway();
        $activityA = $this->repository->createActivity();
        $activityB = $this->repository->createActivity();
        $activityC = $this->repository->createActivity();
        $end = $this->repository->createEndEvent();

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
        $start->createFlowTo($gatewayA, $this->repository);
        $gatewayA
            ->createConditionedFlowTo($activityA, function ($data) {
                return $data['A']=='1';
            }, false, $this->repository)
            ->createConditionedFlowTo($activityB, function ($data) {
                return $data['B']=='1';
            }, false, $this->repository)
            ->createFlowTo($activityC, $this->repository);
        $activityA->createFlowTo($end, $this->repository);
        $activityB->createFlowTo($end, $this->repository);
        $activityC->createFlowTo($end, $this->repository);
        return $process;
    }

    /**
     * Creates a process with default transitions
     *
     * @return \ProcessMaker\Models\Process
     */
    private function createProcessWithExclusiveGatewayAndDefaultTransition()
    {
        $process = $this->repository->createProcess();

        //elements
        $start = $this->repository->createStartEvent();
        $gatewayA = $this->repository->createExclusiveGateway();
        $activityA = $this->repository->createActivity();
        $activityB = $this->repository->createActivity();
        $activityC = $this->repository->createActivity();
        $end = $this->repository->createEndEvent();

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
        $start->createFlowTo($gatewayA, $this->repository);
        $gatewayA
            ->createConditionedFlowTo($activityA, function ($data) {
                return $data['A']=='1';
            }, false, $this->repository)
            ->createConditionedFlowTo($activityB, function ($data) {
                return $data['B']=='1';
            }, false, $this->repository)
            ->createConditionedFlowTo($activityC, function ($data) {
                return true;
            }, true, $this->repository);
        $activityA->createFlowTo($end, $this->repository);
        $activityB->createFlowTo($end, $this->repository);
        $activityC->createFlowTo($end, $this->repository);
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
        $process = $this->repository->createProcess();

        //elements
        $start = $this->repository->createStartEvent();
        $gatewayA = $this->repository->createParallelGateway();
        $activityA = $this->repository->createActivity();
        $activityB = $this->repository->createActivity();
        $activityC = $this->repository->createActivity();
        $gatewayB = $this->repository->createExclusiveGateway();
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
     * Tests the basic functionality of the exclusive gateway
     */
    public function testExclusiveGateway()
    {
        //Create a data store with data.
        $dataStore = $this->repository->createDataStore();
        $dataStore->putData('A', '2');
        $dataStore->putData('B', '1');

        //Load the process
        $process = $this->createProcessWithExclusiveGateway();
        $instance = $this->engine->createExecutionInstance($process, $dataStore);

        //Get References
        $start = $process->getEvents()->item(0);
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
     * Tests that the correct events are triggered when the first flow has a condition evaluated to true
     */
    public function testExclusiveGatewayFirstConditionTrue()
    {
        //Create a data store with data.
        $dataStore = $this->repository->createDataStore();
        $dataStore->putData('A', '1');
        $dataStore->putData('B', '1');

        //Load the process
        $process = $this->createProcessWithExclusiveGatewayAndDefaultTransition();
        $instance = $this->engine->createExecutionInstance($process, $dataStore);

        //Get References
        $start = $process->getEvents()->item(0);

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
    }

    /**
     * Tests the exclusive gateway triggering the default transition
     */
    public function testExclusiveGatewayWithDefaultTransition()
    {
        //Create a data store with data.
        $dataStore = $this->repository->createDataStore();
        $dataStore->putData('A', '2');
        $dataStore->putData('B', '2');

        //Load the process
        $process = $this->createProcessWithExclusiveGatewayAndDefaultTransition();
        $instance = $this->engine->createExecutionInstance($process, $dataStore);

        //Get References
        $start = $process->getEvents()->item(0);
        $activityC = $process->getActivities()->item(2);

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
            DefaultTransition::EVENT_CONDITIONED_TRANSITION,
            GatewayInterface::EVENT_GATEWAY_TOKEN_PASSED,
            ActivityInterface::EVENT_ACTIVITY_ACTIVATED,
        ]);

        //Completes the Activity C
        $tokenC = $activityC->getTokens($instance)->item(0);
        $activityC->complete($tokenC);

        //the run to next state should go false when the max steps is reached.
        $this->assertFalse($this->engine->runToNextState(1));

        $this->engine->runToNextState();

        //Assertion: Verify the triggered engine events. The activity is closed and process is ended.
        $this->assertEvents([
            ActivityInterface::EVENT_ACTIVITY_COMPLETED,
            ActivityInterface::EVENT_ACTIVITY_CLOSED,
            EndEventInterface::EVENT_THROW_TOKEN_ARRIVES,
            EndEventInterface::EVENT_THROW_TOKEN_ARRIVES,
            EndEventInterface::EVENT_THROW_TOKEN_ARRIVES,
            EndEventInterface::EVENT_THROW_TOKEN_CONSUMED,
            EndEventInterface::EVENT_THROW_TOKEN_CONSUMED,
            EndEventInterface::EVENT_THROW_TOKEN_CONSUMED,
            EndEventInterface::EVENT_EVENT_TRIGGERED,
            ProcessInterface::EVENT_PROCESS_INSTANCE_COMPLETED,
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
        $dataStore = $this->repository->createDataStore();

        //Load the process
        $process = $this->createParallelDivergingExclusiveConverging();
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
            GatewayInterface::EVENT_GATEWAY_ACTIVATED,
            GatewayInterface::EVENT_GATEWAY_TOKEN_CONSUMED,
            TransitionInterface::EVENT_CONDITIONED_TRANSITION,
            GatewayInterface::EVENT_GATEWAY_TOKEN_PASSED,
            ActivityInterface::EVENT_ACTIVITY_ACTIVATED,
        ]);

        //Completes the Activity B
        $tokenB = $activityB->getTokens($instance)->item(0);
        $activityB->complete($tokenB);
        $this->engine->runToNextState();

        //Assertion: Verify the triggered engine events. The activity B is closed.
        $this->assertEvents([
            ActivityInterface::EVENT_ACTIVITY_COMPLETED,
            ActivityInterface::EVENT_ACTIVITY_CLOSED,
            GatewayInterface::EVENT_GATEWAY_TOKEN_ARRIVES,
            GatewayInterface::EVENT_GATEWAY_ACTIVATED,
            GatewayInterface::EVENT_GATEWAY_TOKEN_CONSUMED,
            TransitionInterface::EVENT_CONDITIONED_TRANSITION,
            GatewayInterface::EVENT_GATEWAY_TOKEN_PASSED,
            ActivityInterface::EVENT_ACTIVITY_ACTIVATED,
        ]);

        //Assertion: ActivityC has two tokens.
        $this->assertEquals(2, $activityC->getTokens($instance)->count());

        //Completes the Activity C for the first token
        $tokenC = $activityC->getTokens($instance)->item(0);
        $activityC->complete($tokenC);
        $this->engine->runToNextState();

        //Completes the Activity C for the next token
        $tokenC = $activityC->getTokens($instance)->item(0);
        $activityC->complete($tokenC);
        $this->engine->runToNextState();

        //Assertion: ActivityC has no tokens.
        $this->assertEquals(0, $activityC->getTokens($instance)->count());

        //Assertion: ActivityC was completed and closed per each token, then the end event was triggered twice.
        $this->assertEvents([
            ActivityInterface::EVENT_ACTIVITY_COMPLETED,
            ActivityInterface::EVENT_ACTIVITY_CLOSED,
            EndEventInterface::EVENT_THROW_TOKEN_ARRIVES,
            EndEventInterface::EVENT_THROW_TOKEN_CONSUMED,
            EndEventInterface::EVENT_EVENT_TRIGGERED,
            ActivityInterface::EVENT_ACTIVITY_COMPLETED,
            ActivityInterface::EVENT_ACTIVITY_CLOSED,
            EndEventInterface::EVENT_THROW_TOKEN_ARRIVES,
            EndEventInterface::EVENT_THROW_TOKEN_CONSUMED,
            EndEventInterface::EVENT_EVENT_TRIGGERED,
            ProcessInterface::EVENT_PROCESS_INSTANCE_COMPLETED,
        ]);
    }

    /**
     * Test exclusive gateway with custom data
     *
     */
    public function testConditionalExclusiveParameters()
    {
        //Load a BpmnFile Repository
        $bpmnRepository = new BpmnDocument();
        $bpmnRepository->setEngine($this->engine);
        $bpmnRepository->setFactory($this->repository);

        $bpmnRepository->load(__DIR__ . '/files/ExclusiveGateway.bpmn');

        //Create a data store with data.
        $dataStore = $this->repository->createDataStore();
        $dataStore->putData('Age', '8');

        //Load a process from a bpmn repository by Id
        $process = $bpmnRepository->getProcess('ExclusiveGatewayProcess');
        $instance = $this->engine->createExecutionInstance($process, $dataStore);

        //Get start event and event definition references
        $start = $bpmnRepository->getStartEvent('StartEvent');
        $activity1 = $bpmnRepository->getStartEvent('Exclusive1');


        //Start the process
        $start->start($instance);
        $this->engine->runToNextState();

        // Advance the first activity
        $token0 = $activity1->getTokens($instance)->item(0);
        $activity1->complete($token0);
        $this->engine->runToNextState();
    }
}
