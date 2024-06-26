<?php

namespace Tests\Feature\Engine;

use Exception;
use ProcessMaker\Nayra\Bpmn\Models\Flow;
use ProcessMaker\Nayra\Contracts\Bpmn\ActivityInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\EndEventInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\EventInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\GatewayInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\ProcessInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\ScriptTaskInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\TransitionInterface;
use ProcessMaker\Nayra\Storage\BpmnDocument;

/**
 * Test transitions
 */
class ExclusiveGatewayDebugTest extends EngineTestCase
{
    /**
     * Creates a process where the exclusive gateway has conditioned and simple transitions
     *           ┌─────────┐
     *        ┌─→│activityA│─┐
     *        │  └─────────┘ │
     *  ○─→╱╲─┘  ┌─────────┐ |
     *     ╲╱─┐  │activityB│ +─→●
     *        └─→└─────────┘ |
     *        └─→┌─────────┐ |
     *           │activityC│─┘
     *           └─────────┘
     * @return \ProcessMaker\Models\Process|ProcessInterface
     */
    private function createProcessWithExclusiveGateway()
    {
        $process = $this->repository->createProcess();

        //elements
        $start = $this->repository->createStartEvent();
        $gatewayA = $this->repository->createExclusiveGateway();
        $activityA = $this->repository->createActivity()->setName('Activity A');
        $activityB = $this->repository->createActivity()->setName('Activity B');
        $activityC = $this->repository->createActivity()->setName('Activity C');
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
                return $data['A'] == '1';
            }, false, $this->repository)
            ->createConditionedFlowTo($activityB, function ($data) {
                return $data['B'] == '1';
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
        $activityA = $this->repository->createActivity()->setName('Activity A');
        $activityB = $this->repository->createActivity()->setName('Activity B');
        $activityC = $this->repository->createActivity()->setName('Activity C');
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
            ->createConditionedFlowTo($activityC, function ($data) {
                return true;
            }, true, $this->repository)
            ->createConditionedFlowTo($activityA, function ($data) {
                return $data['A'] == '1';
            }, false, $this->repository)
            ->createConditionedFlowTo($activityB, function ($data) {
                return $data['B'] == '1';
            }, false, $this->repository);
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
        // Create a data store with data.
        $dataStore = $this->repository->createDataStore();
        $dataStore->putData('A', '2');
        $dataStore->putData('B', '1'); // Condition for activity B is true

        // Enable demo mode
        $this->engine->setDemoMode(true);

        // Run the process
        $process = $this->createProcessWithExclusiveGateway();
        $instance = $this->engine->createExecutionInstance($process, $dataStore);
        $start = $process->getEvents()->item(0);
        $start->start($instance);
        $this->engine->runToNextState();

        // Assertion: The process paused in the gateway
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

        // Assertion: Engine runs to the next state because a flow was selected manually
        $this->assertEvents([
            GatewayInterface::EVENT_GATEWAY_ACTIVATED,
            GatewayInterface::EVENT_GATEWAY_TOKEN_CONSUMED,
            TransitionInterface::EVENT_CONDITIONED_TRANSITION,
            GatewayInterface::EVENT_GATEWAY_TOKEN_PASSED,
            ActivityInterface::EVENT_ACTIVITY_ACTIVATED,
        ]);

        // Assertion: The Activity connected to the selected flow was activated
        $activeTask = $instance->getTokens()->item(0)->getOwnerElement();
        $expectedTask = $selectedFlow->getTarget();
        $this->assertEquals($expectedTask->getName(), $activeTask->getName());
    }

    /**
     * Tests that the correct events are triggered when the first flow has a condition evaluated to true
     */
    public function testExclusiveGatewayFirstConditionTrue()
    {
        //Create a data store with data.
        $dataStore = $this->repository->createDataStore();
        $dataStore->putData('A', '1'); // Condition for activity A is true
        $dataStore->putData('B', '1');

        // Enable demo mode
        $this->engine->setDemoMode(true);

        // Run the process
        $process = $this->createProcessWithExclusiveGatewayAndDefaultTransition();
        $instance = $this->engine->createExecutionInstance($process, $dataStore);
        $start = $process->getEvents()->item(0);
        $start->start($instance);
        $this->engine->runToNextState();

        // Assertion: The process paused in the gateway
        $this->assertEvents([
            ProcessInterface::EVENT_PROCESS_INSTANCE_CREATED,
            EventInterface::EVENT_EVENT_TRIGGERED,
            GatewayInterface::EVENT_GATEWAY_TOKEN_ARRIVES,
        ]);

        // Select the flow for the gateway and run the engine
        $gateway = $process->getGateways()->item(0);
        $selectedFlow = $gateway->getOutgoingFlows()->item(1);
        $this->engine->setSelectedDemoFlow($gateway, $selectedFlow);
        $this->engine->runToNextState();

        // Assertion: Engine runs to the next state because a flow was selected manually
        $this->assertEvents([
            GatewayInterface::EVENT_GATEWAY_ACTIVATED,
            GatewayInterface::EVENT_GATEWAY_TOKEN_CONSUMED,
            TransitionInterface::EVENT_CONDITIONED_TRANSITION,
            GatewayInterface::EVENT_GATEWAY_TOKEN_PASSED,
            ActivityInterface::EVENT_ACTIVITY_ACTIVATED,
        ]);

        // Assertion: The Activity connected to the selected flow was activated
        $activeTask = $instance->getTokens()->item(0)->getOwnerElement();
        $expectedTask = $selectedFlow->getTarget();
        $this->assertEquals($expectedTask->getName(), $activeTask->getName());
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

        // Enable demo mode
        $this->engine->setDemoMode(true);

        // Run the process
        $process = $this->createProcessWithExclusiveGatewayAndDefaultTransition();
        $instance = $this->engine->createExecutionInstance($process, $dataStore);
        $start = $process->getEvents()->item(0);
        $start->start($instance);
        $this->engine->runToNextState();

        // Assertion: The process paused in the gateway
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

        // Assertion: Engine runs to the next state because a flow was selected manually
        $this->assertEvents([
            GatewayInterface::EVENT_GATEWAY_ACTIVATED,
            GatewayInterface::EVENT_GATEWAY_TOKEN_CONSUMED,
            TransitionInterface::EVENT_CONDITIONED_TRANSITION,
            GatewayInterface::EVENT_GATEWAY_TOKEN_PASSED,
            ActivityInterface::EVENT_ACTIVITY_ACTIVATED,
        ]);

        // Assertion: The Activity connected to the selected flow was activated
        $activeTask = $instance->getTokens()->item(0)->getOwnerElement();
        $expectedTask = $selectedFlow->getTarget();
        $this->assertEquals($expectedTask?->getName(), $activeTask->getName());
    }

    /**
     * Tests the exclusive gateway triggering the default transition with a
     * demo mode to an invalid Flow that is not connected to the gateway
     */
    public function testExclusiveGatewayWithDefaultTransitionInvalidDemoFlow()
    {
        //Create a data store with data.
        $dataStore = $this->repository->createDataStore();
        $dataStore->putData('A', '2');
        $dataStore->putData('B', '2');

        // Enable demo mode
        $this->engine->setDemoMode(true);

        // Run the process
        $process = $this->createProcessWithExclusiveGatewayAndDefaultTransition();
        $instance = $this->engine->createExecutionInstance($process, $dataStore);
        $start = $process->getEvents()->item(0);
        $start->start($instance);
        $this->engine->runToNextState();

        // Assertion: The process paused in the gateway
        $this->assertEvents([
            ProcessInterface::EVENT_PROCESS_INSTANCE_CREATED,
            EventInterface::EVENT_EVENT_TRIGGERED,
            GatewayInterface::EVENT_GATEWAY_TOKEN_ARRIVES,
        ]);

        // Select an invalid flow for the gateway and run the engine
        $gateway = $process->getGateways()->item(0);
        $selectedFlow = new Flow();
        $this->engine->setSelectedDemoFlow($gateway, $selectedFlow);
        $this->engine->runToNextState();

        // Assertion: Engine triggers the gateway but stop because the selected flow is invalid
        $this->assertEvents([
            GatewayInterface::EVENT_GATEWAY_ACTIVATED,
            GatewayInterface::EVENT_GATEWAY_TOKEN_CONSUMED,
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
        // Create a data store with data.
        $dataStore = $this->repository->createDataStore();

        // Load the process
        $process = $this->createParallelDivergingExclusiveConverging();
        $instance = $this->engine->createExecutionInstance($process, $dataStore);

        // Get References
        $start = $process->getEvents()->item(0);
        $activityA = $process->getActivities()->item(0);
        $activityB = $process->getActivities()->item(1);
        $activityC = $process->getActivities()->item(2);

        // Start the process
        $start->start($instance);

        $this->engine->runToNextState();

        // Assertion: Verify the triggered engine events. Two activities are activated.
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

        // Enable demo mode
        $this->engine->setDemoMode(true);

        // Completes the Activity A
        $tokenA = $activityA->getTokens($instance)->item(0);
        $activityA->complete($tokenA);
        $this->engine->runToNextState();

        // Assertion: Verify the triggered engine events. The activity is closed and the gateway is activated.
        $this->assertEvents([
            ActivityInterface::EVENT_ACTIVITY_COMPLETED,
            ActivityInterface::EVENT_ACTIVITY_CLOSED,
            GatewayInterface::EVENT_GATEWAY_TOKEN_ARRIVES,
        ]);

        // Completes the Activity B
        $tokenB = $activityB->getTokens($instance)->item(0);
        $activityB->complete($tokenB);
        $this->engine->runToNextState();

        // Assertion: Verify the triggered engine events. The activity B is closed and the gateway is activated.
        $this->assertEvents([
            ActivityInterface::EVENT_ACTIVITY_COMPLETED,
            ActivityInterface::EVENT_ACTIVITY_CLOSED,
            GatewayInterface::EVENT_GATEWAY_TOKEN_ARRIVES,
        ]);

        // Select the flow for the gateway B (exclusive one) and run the engine
        $gatewayB = $process->getGateways()->item(1);
        $selectedFlow = $gatewayB->getOutgoingFlows()->item(0);
        $this->engine->setSelectedDemoFlow($gatewayB, $selectedFlow);
        $this->engine->runToNextState();

        $this->assertEvents([
            // First token passes through the gateway
            GatewayInterface::EVENT_GATEWAY_ACTIVATED,
            GatewayInterface::EVENT_GATEWAY_TOKEN_CONSUMED,
            TransitionInterface::EVENT_CONDITIONED_TRANSITION,
            GatewayInterface::EVENT_GATEWAY_TOKEN_PASSED,
            ActivityInterface::EVENT_ACTIVITY_ACTIVATED,

            // Second token passes through the gateway
            GatewayInterface::EVENT_GATEWAY_ACTIVATED,
            GatewayInterface::EVENT_GATEWAY_TOKEN_CONSUMED,
            TransitionInterface::EVENT_CONDITIONED_TRANSITION,
            GatewayInterface::EVENT_GATEWAY_TOKEN_PASSED,
            ActivityInterface::EVENT_ACTIVITY_ACTIVATED,
        ]);

        // Assertion: ActivityC has two tokens.
        $this->assertEquals(2, $activityC->getTokens($instance)->count());
        
        // Continue with the execution completing Activity C
        // Completes the Activity C for the first token
        $tokenC = $activityC->getTokens($instance)->item(0);
        $activityC->complete($tokenC);
        $this->engine->runToNextState();

        // Completes the Activity C for the next token
        $tokenC = $activityC->getTokens($instance)->item(0);
        $activityC->complete($tokenC);
        $this->engine->runToNextState();

        // Assertion: ActivityC has no tokens.
        $this->assertEquals(0, $activityC->getTokens($instance)->count());

        // Assertion: ActivityC was completed and closed per each token, then the end event was triggered twice.
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
     * Test exclusive gateway with custom data and script task
     */
    public function testConditionalExclusiveParameters()
    {
        // Load a BpmnFile Repository
        $bpmnRepository = new BpmnDocument();
        $bpmnRepository->setEngine($this->engine);
        $bpmnRepository->setFactory($this->repository);

        $bpmnRepository->load(__DIR__ . '/files/ExclusiveGateway.bpmn');

        // Enable demo mode
        $this->engine->setDemoMode(true);

        // Run the process with custom data
        $dataStore = $this->repository->createDataStore();
        $dataStore->putData('Age', '8');
        $process = $bpmnRepository->getProcess('ExclusiveGatewayProcess');
        $instance = $this->engine->createExecutionInstance($process, $dataStore);
        $start = $bpmnRepository->getStartEvent('StartEvent');
        $activity1 = $bpmnRepository->getStartEvent('Exclusive1');
        $start->start($instance);
        $this->engine->runToNextState();

        // Complete the first activity
        $token0 = $activity1->getTokens($instance)->item(0);
        $activity1->complete($token0);
        $this->engine->runToNextState();

        // Assertion: Process started, activity completed, gateway executed, script activated
        $this->assertEvents([
            ProcessInterface::EVENT_PROCESS_INSTANCE_CREATED,
            EventInterface::EVENT_EVENT_TRIGGERED,
            ActivityInterface::EVENT_ACTIVITY_ACTIVATED,
            ScriptTaskInterface::EVENT_SCRIPT_TASK_ACTIVATED,
            ActivityInterface::EVENT_ACTIVITY_COMPLETED,
            ActivityInterface::EVENT_ACTIVITY_CLOSED,
            GatewayInterface::EVENT_GATEWAY_TOKEN_ARRIVES,
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
            TransitionInterface::EVENT_CONDITIONED_TRANSITION,
            GatewayInterface::EVENT_GATEWAY_TOKEN_PASSED,
            ActivityInterface::EVENT_ACTIVITY_ACTIVATED,
            ScriptTaskInterface::EVENT_SCRIPT_TASK_ACTIVATED,
        ]);
    }

    /**
     * Test demo mode with runtime error when evaluating an exclusive gateway
     */
    public function testExclusiveGatewayMissingVariable()
    {
        // Enable demo mode
        $this->engine->setDemoMode(true);

        // Create a data store with data.
        $dataStore = $this->repository->createDataStore();

        // Load the process
        $process = $this->createProcessWithExclusiveGateway();
        $instance = $this->engine->createExecutionInstance($process, $dataStore);

        // simulate a formal expression runtime error when evaluating a gateway condition
        $gatewayA = $process->getGateways()->item(0);
        $gatewayA->getConditionedTransitions()->item(0)->setCondition(function ($data) {
            throw new Exception('Variable A is missing');
        });

        // Get References
        $start = $process->getEvents()->item(0);

        // Run the process
        $start->start($instance);
        $this->engine->runToNextState();

        // Assertion: No RuntimeException expected, Gateway activated
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

        // Assertion: No RuntimeException expected, gateway activated and continue with the process
        $this->assertEvents([
            GatewayInterface::EVENT_GATEWAY_ACTIVATED,
            GatewayInterface::EVENT_GATEWAY_TOKEN_CONSUMED,
            TransitionInterface::EVENT_CONDITIONED_TRANSITION,
            GatewayInterface::EVENT_GATEWAY_TOKEN_PASSED,
            ActivityInterface::EVENT_ACTIVITY_ACTIVATED,
        ]);
    }
}
