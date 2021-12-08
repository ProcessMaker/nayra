<?php

namespace Tests\Feature\Engine;

use ProcessMaker\Nayra\Bpmn\Models\DatePeriod;
use ProcessMaker\Nayra\Bpmn\Models\ErrorEventDefinition;
use ProcessMaker\Nayra\Contracts\Bpmn\ActivityInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\BoundaryEventInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\CallActivityInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\EndEventInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\EventInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\GatewayInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\IntermediateCatchEventInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\IntermediateThrowEventInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\ProcessInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\ScriptTaskInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\SignalEventDefinitionInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\StartEventInterface;
use ProcessMaker\Nayra\Contracts\Engine\JobManagerInterface;
use ProcessMaker\Nayra\Storage\BpmnDocument;

/**
 * Tests for the BoundaryEvent elements
 *
 */
class BoundaryEventTest extends EngineTestCase
{
    /**
     * Tests a process with a signal boundary event attached to a task
     */
    public function testSignalBoundaryEvent()
    {
        // Load the process from a BPMN file
        $bpmnRepository = new BpmnDocument();
        $bpmnRepository->setEngine($this->engine);
        $bpmnRepository->setFactory($this->repository);
        $bpmnRepository->load(__DIR__ . '/files/Signal_BoundaryEvent.bpmn');

        // Get the process by Id
        $process = $bpmnRepository->getProcess('PROCESS_1');
        $dataStore = $this->repository->createDataStore();

        // Create an instance of the process
        $instance = $this->engine->createExecutionInstance($process, $dataStore);

        // Get References
        $start = $process->getEvents()->item(0);
        $task1 = $bpmnRepository->getActivity('_5');
        $task2 = $bpmnRepository->getActivity('_7');
        $task3 = $bpmnRepository->getActivity('_12');

        // Start a process instance
        $start->start($instance);
        $this->engine->runToNextState();

        // Assertion: The process is started and two tasks were activated
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

        // Complete second task
        $task2->complete($task2->getTokens($instance)->item(0));
        $this->engine->runToNextState();

        // Assertion: Task 2 is completed, an end event Signal is thrown, then caught by the boundary event
        $this->assertEvents([
            ActivityInterface::EVENT_ACTIVITY_COMPLETED,
            ActivityInterface::EVENT_ACTIVITY_CLOSED,
            EndEventInterface::EVENT_THROW_TOKEN_ARRIVES,
            SignalEventDefinitionInterface::EVENT_THROW_EVENT_DEFINITION,
            BoundaryEventInterface::EVENT_BOUNDARY_EVENT_CATCH,
            BoundaryEventInterface::EVENT_BOUNDARY_EVENT_CONSUMED,
            ActivityInterface::EVENT_ACTIVITY_CANCELLED,
            EndEventInterface::EVENT_THROW_TOKEN_CONSUMED,
            EndEventInterface::EVENT_EVENT_TRIGGERED,
            ActivityInterface::EVENT_ACTIVITY_ACTIVATED,
        ]);

        // Assertion: Task 1 does not have tokens
        $this->assertEquals(0, $task1->getTokens($instance)->count());

        // Assertion: Task 3 has one token
        $this->assertEquals(1, $task3->getTokens($instance)->count());
    }

    /**
     * Tests a process with a cycle timer boundary event attached to a task
     */
    public function testCycleTimerBoundaryEvent()
    {
        // Load the process from a BPMN file
        $bpmnRepository = new BpmnDocument();
        $bpmnRepository->setEngine($this->engine);
        $bpmnRepository->setFactory($this->repository);
        $bpmnRepository->load(__DIR__ . '/files/Timer_BoundaryEvent_Cycle.bpmn');

        // Get the process by Id
        $process = $bpmnRepository->getProcess('PROCESS_1');
        $dataStore = $this->repository->createDataStore();

        // Create an instance of the process
        $instance = $this->engine->createExecutionInstance($process, $dataStore);

        // Get References
        $start = $bpmnRepository->getStartEvent('_2');
        $task1 = $bpmnRepository->getActivity('_5');
        $task2 = $bpmnRepository->getActivity('_12');
        $boundaryEvent = $bpmnRepository->getBoundaryEvent('_11');

        // Start a process instance
        $start->start($instance);
        $this->engine->runToNextState();

        // Assertion: The process is started, a task is activated and a timer event scheduled
        $this->assertEvents([
            ProcessInterface::EVENT_PROCESS_INSTANCE_CREATED,
            EventInterface::EVENT_EVENT_TRIGGERED,
            ActivityInterface::EVENT_ACTIVITY_ACTIVATED,
            JobManagerInterface::EVENT_SCHEDULE_CYCLE,
        ]);

        // Boundary event is attached to $task1, its token is associated with the timer event
        $activeToken = $task1->getTokens($instance)->item(0);

        // Assertion: The jobs manager receive a scheduling request to trigger the start event time cycle specified in the process
        $this->assertScheduledCyclicTimer(new DatePeriod('R4/2018-05-01T00:00:00Z/PT1M'), $boundaryEvent, $activeToken);

        // Trigger the boundary event
        $boundaryEvent->execute($boundaryEvent->getEventDefinitions()->item(0), $instance);
        $this->engine->runToNextState();

        // Assertion: Boundary event was caught, the first task cancelled and continue to the task 2
        $this->assertEvents([
            BoundaryEventInterface::EVENT_BOUNDARY_EVENT_CATCH,
            BoundaryEventInterface::EVENT_BOUNDARY_EVENT_CONSUMED,
            ActivityInterface::EVENT_ACTIVITY_CANCELLED,
            ActivityInterface::EVENT_ACTIVITY_ACTIVATED,
        ]);

        // Complete second task
        $task2->complete($task2->getTokens($instance)->item(0));
        $this->engine->runToNextState();

        // Assertion: Task 2 is completed, and the process is completed
        $this->assertEvents([
            ActivityInterface::EVENT_ACTIVITY_COMPLETED,
            ActivityInterface::EVENT_ACTIVITY_CLOSED,
            EndEventInterface::EVENT_THROW_TOKEN_ARRIVES,
            EndEventInterface::EVENT_THROW_TOKEN_CONSUMED,
            EndEventInterface::EVENT_EVENT_TRIGGERED,
            ProcessInterface::EVENT_PROCESS_INSTANCE_COMPLETED,
        ]);
    }

    /**
     * Tests a process with an error boundary event attached to a script task
     */
    public function testErrorBoundaryEventScript1Task()
    {
        // Load the process from a BPMN file
        $bpmnRepository = new BpmnDocument();
        $bpmnRepository->setEngine($this->engine);
        $bpmnRepository->setFactory($this->repository);
        $bpmnRepository->load(__DIR__ . '/files/Error_BoundaryEvent_ScriptTask.bpmn');

        // Get the process by Id
        $process = $bpmnRepository->getProcess('PROCESS_1');
        $dataStore = $this->repository->createDataStore();

        // Create an instance of the process
        $instance = $this->engine->createExecutionInstance($process, $dataStore);

        // Get References
        $start = $bpmnRepository->getStartEvent('_2');
        $task1 = $bpmnRepository->getScriptTask('_5');
        $task2 = $bpmnRepository->getActivity('_12');

        // Start a process instance
        $start->start($instance);
        $this->engine->runToNextState();

        // Assertion: The process is started and the script activated
        $this->assertEvents([
            ProcessInterface::EVENT_PROCESS_INSTANCE_CREATED,
            EventInterface::EVENT_EVENT_TRIGGERED,
            ActivityInterface::EVENT_ACTIVITY_ACTIVATED,
            ScriptTaskInterface::EVENT_SCRIPT_TASK_ACTIVATED,
        ]);

        // Run the script task
        $activeToken = $task1->getTokens($instance)->item(0);
        $task1->runScript($activeToken);
        $this->engine->runToNextState();

        // Assertion: Script task throws an exception
        $this->assertEvents([
            ScriptTaskInterface::EVENT_ACTIVITY_EXCEPTION,
            BoundaryEventInterface::EVENT_BOUNDARY_EVENT_CATCH,
            ActivityInterface::EVENT_ACTIVITY_CANCELLED,
            BoundaryEventInterface::EVENT_BOUNDARY_EVENT_CONSUMED,
            ActivityInterface::EVENT_ACTIVITY_ACTIVATED,
        ]);

        // Complete second task
        $task2->complete($task2->getTokens($instance)->item(0));
        $this->engine->runToNextState();

        // Assertion: Task 2 is completed, and the process is completed
        $this->assertEvents([
            ActivityInterface::EVENT_ACTIVITY_COMPLETED,
            ActivityInterface::EVENT_ACTIVITY_CLOSED,
            EndEventInterface::EVENT_THROW_TOKEN_ARRIVES,
            EndEventInterface::EVENT_THROW_TOKEN_CONSUMED,
            EndEventInterface::EVENT_EVENT_TRIGGERED,
            ProcessInterface::EVENT_PROCESS_INSTANCE_COMPLETED,
        ]);
    }

    /**
     * Tests a process with a error boundary event attached to a CallActivity
     */
    public function testErrorBoundaryEventCallActivity()
    {
        // Load the process from a BPMN file
        $bpmnRepository = new BpmnDocument();
        $bpmnRepository->setEngine($this->engine);
        $bpmnRepository->setFactory($this->repository);
        $bpmnRepository->load(__DIR__ . '/files/Error_BoundaryEvent_CallActivity.bpmn');

        // Get the process by Id
        $process = $bpmnRepository->getProcess('PROCESS_1');
        $dataStore = $this->repository->createDataStore();

        // Create an instance of the process
        $instance = $this->engine->createExecutionInstance($process, $dataStore);

        // Get References
        $start = $bpmnRepository->getStartEvent('_4');
        $task1 = $bpmnRepository->getCallActivity('_7');
        $task2 = $bpmnRepository->getActivity('_13');

        // Start a process instance
        $start->start($instance);
        $this->engine->runToNextState();

        // Assertion: The process is started, then the sub process throw and ErrorEvent, catch by the BoundaryEvent
        $this->assertEvents([
            ProcessInterface::EVENT_PROCESS_INSTANCE_CREATED,
            EventInterface::EVENT_EVENT_TRIGGERED,
            ActivityInterface::EVENT_ACTIVITY_ACTIVATED,

            ProcessInterface::EVENT_PROCESS_INSTANCE_CREATED,
            EndEventInterface::EVENT_EVENT_TRIGGERED,
            EndEventInterface::EVENT_THROW_TOKEN_ARRIVES,
            ErrorEventDefinition::EVENT_THROW_EVENT_DEFINITION,
            EndEventInterface::EVENT_THROW_TOKEN_CONSUMED,
            EndEventInterface::EVENT_EVENT_TRIGGERED,
            ProcessInterface::EVENT_PROCESS_INSTANCE_COMPLETED,
            ActivityInterface::EVENT_ACTIVITY_EXCEPTION,
            BoundaryEventInterface::EVENT_BOUNDARY_EVENT_CATCH,

            ActivityInterface::EVENT_ACTIVITY_CANCELLED,
            BoundaryEventInterface::EVENT_BOUNDARY_EVENT_CONSUMED,
            ActivityInterface::EVENT_ACTIVITY_ACTIVATED,
        ]);

        // Complete second task
        $task2->complete($task2->getTokens($instance)->item(0));
        $this->engine->runToNextState();

        // Assertion: Task 2 is completed, and the process is completed
        $this->assertEvents([
            ActivityInterface::EVENT_ACTIVITY_COMPLETED,
            ActivityInterface::EVENT_ACTIVITY_CLOSED,
            EndEventInterface::EVENT_THROW_TOKEN_ARRIVES,
            EndEventInterface::EVENT_THROW_TOKEN_CONSUMED,
            EndEventInterface::EVENT_EVENT_TRIGGERED,
            ProcessInterface::EVENT_PROCESS_INSTANCE_COMPLETED,
        ]);
    }

    /**
     * Tests a process with a cycle timer boundary event attached to a CallActivity
     */
    public function testCycleTimerBoundaryEventCallActivity()
    {
        // Load the process from a BPMN file
        $bpmnRepository = new BpmnDocument();
        $bpmnRepository->setEngine($this->engine);
        $bpmnRepository->setFactory($this->repository);
        $bpmnRepository->load(__DIR__ . '/files/Timer_BoundaryEvent_CallActivity.bpmn');

        // Get the process by Id
        $process = $bpmnRepository->getProcess('PROCESS_1');
        $dataStore = $this->repository->createDataStore();

        // Create an instance of the process
        $instance = $this->engine->createExecutionInstance($process, $dataStore);

        // Get References
        $start = $bpmnRepository->getStartEvent('_4');
        $task1 = $bpmnRepository->getCallActivity('_7');
        $task2 = $bpmnRepository->getActivity('_13');
        $boundaryEvent = $bpmnRepository->getBoundaryEvent('_12');

        // Start a process instance
        $start->start($instance);
        $this->engine->runToNextState();

        // Assertion: The process is started, a task is activated, the sub process is started and a timer event scheduled
        $this->assertEvents([
            ProcessInterface::EVENT_PROCESS_INSTANCE_CREATED,
            EventInterface::EVENT_EVENT_TRIGGERED,
            ActivityInterface::EVENT_ACTIVITY_ACTIVATED,
            ProcessInterface::EVENT_PROCESS_INSTANCE_CREATED,
            JobManagerInterface::EVENT_SCHEDULE_CYCLE,
            StartEventInterface::EVENT_EVENT_TRIGGERED,
            ActivityInterface::EVENT_ACTIVITY_ACTIVATED,
        ]);

        // Boundary event is attached to $task1, its token is associated with the timer event
        $activeToken = $task1->getTokens($instance)->item(0);

        // Assertion: The jobs manager receive a scheduling request to trigger the start event time cycle specified in the process
        $this->assertScheduledCyclicTimer(new DatePeriod('R4/2018-05-01T00:00:00Z/PT1M'), $boundaryEvent, $activeToken);

        // Trigger the boundary event
        $boundaryEvent->execute($boundaryEvent->getEventDefinitions()->item(0), $instance);
        $this->engine->runToNextState();

        // Assertion: Boundary event was caught, call activity and subprocess is cancelled, then continue to the task 2
        $this->assertEvents([
            BoundaryEventInterface::EVENT_BOUNDARY_EVENT_CATCH,
            BoundaryEventInterface::EVENT_BOUNDARY_EVENT_CONSUMED,
            ActivityInterface::EVENT_ACTIVITY_CANCELLED,
            ActivityInterface::EVENT_ACTIVITY_ACTIVATED,
            ActivityInterface::EVENT_ACTIVITY_CANCELLED,
            ProcessInterface::EVENT_PROCESS_INSTANCE_COMPLETED,
        ]);

        // Complete second task
        $task2->complete($task2->getTokens($instance)->item(0));
        $this->engine->runToNextState();

        // Assertion: Task 2 is completed, and the process is completed
        $this->assertEvents([
            ActivityInterface::EVENT_ACTIVITY_COMPLETED,
            ActivityInterface::EVENT_ACTIVITY_CLOSED,
            EndEventInterface::EVENT_THROW_TOKEN_ARRIVES,
            EndEventInterface::EVENT_THROW_TOKEN_CONSUMED,
            EndEventInterface::EVENT_EVENT_TRIGGERED,
            ProcessInterface::EVENT_PROCESS_INSTANCE_COMPLETED,
        ]);
    }

    /**
     * Tests the a process with a signal boundary event attached to a CallActivity
     */
    public function testSignalBoundaryEventCallActivity()
    {
        // Load the process from a BPMN file
        $bpmnRepository = new BpmnDocument();
        $bpmnRepository->setEngine($this->engine);
        $bpmnRepository->setFactory($this->repository);
        $bpmnRepository->load(__DIR__ . '/files/Signal_BoundaryEvent_CallActivity.bpmn');

        // Get the process by Id
        $process = $bpmnRepository->getProcess('PROCESS_1');
        $dataStore = $this->repository->createDataStore();

        // Create an instance of the process
        $instance = $this->engine->createExecutionInstance($process, $dataStore);

        // Get References
        $start = $bpmnRepository->getStartEvent('_4');
        $task1 = $bpmnRepository->getCallActivity('_7');
        $task2 = $bpmnRepository->getActivity('_13');

        // Start a process instance
        $start->start($instance);
        $this->engine->runToNextState();

        // Assertion: The process is started and two tasks were activated
        $this->assertEvents([
            ProcessInterface::EVENT_PROCESS_INSTANCE_CREATED,
            EventInterface::EVENT_EVENT_TRIGGERED,
            ActivityInterface::EVENT_ACTIVITY_ACTIVATED,
            ProcessInterface::EVENT_PROCESS_INSTANCE_CREATED,
            StartEventInterface::EVENT_EVENT_TRIGGERED,
            BoundaryEventInterface::EVENT_BOUNDARY_EVENT_CATCH,
            IntermediateThrowEventInterface::EVENT_THROW_TOKEN_ARRIVES,
            IntermediateThrowEventInterface::EVENT_THROW_TOKEN_CONSUMED,
            IntermediateThrowEventInterface::EVENT_THROW_TOKEN_PASSED,
            ActivityInterface::EVENT_ACTIVITY_ACTIVATED,
            BoundaryEventInterface::EVENT_BOUNDARY_EVENT_CONSUMED,
            ActivityInterface::EVENT_ACTIVITY_CANCELLED,
            ActivityInterface::EVENT_ACTIVITY_ACTIVATED,
            CallActivityInterface::EVENT_ACTIVITY_CANCELLED,
            ProcessInterface::EVENT_PROCESS_INSTANCE_COMPLETED,
        ]);

        // Complete second task
        $task2->complete($task2->getTokens($instance)->item(0));
        $this->engine->runToNextState();

        // Assertion: Task 2 is completed, and the process is completed
        $this->assertEvents([
            ActivityInterface::EVENT_ACTIVITY_COMPLETED,
            ActivityInterface::EVENT_ACTIVITY_CLOSED,
            EndEventInterface::EVENT_THROW_TOKEN_ARRIVES,
            EndEventInterface::EVENT_THROW_TOKEN_CONSUMED,
            EndEventInterface::EVENT_EVENT_TRIGGERED,
            ProcessInterface::EVENT_PROCESS_INSTANCE_COMPLETED,
        ]);
    }

    /**
     * Tests a process with a signal boundary non interrupting event attached to a task
     */
    public function testSignalBoundaryEventNonInterrupting()
    {
        // Load the process from a BPMN file
        $bpmnRepository = new BpmnDocument();
        $bpmnRepository->setEngine($this->engine);
        $bpmnRepository->setFactory($this->repository);
        $bpmnRepository->load(__DIR__ . '/files/Signal_BoundaryEvent_NonInterrupting.bpmn');

        // Get the process by Id
        $process = $bpmnRepository->getProcess('PROCESS_1');
        $dataStore = $this->repository->createDataStore();

        // Create an instance of the process
        $instance = $this->engine->createExecutionInstance($process, $dataStore);

        // Get References
        $start = $process->getEvents()->item(0);
        $task1 = $bpmnRepository->getActivity('_5');
        $task2 = $bpmnRepository->getActivity('_7');
        $task3 = $bpmnRepository->getActivity('_12');
        $task4 = $bpmnRepository->getActivity('_15');

        // Start a process instance
        $start->start($instance);
        $this->engine->runToNextState();

        // Assertion: The process is started and two tasks were activated
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

        // Complete second task
        $task2->complete($task2->getTokens($instance)->item(0));
        $this->engine->runToNextState();

        // Assertion: Task 2 is completed, an end event Signal is thrown, then caught by the boundary event
        $this->assertEvents([
            ActivityInterface::EVENT_ACTIVITY_COMPLETED,
            ActivityInterface::EVENT_ACTIVITY_CLOSED,
            EndEventInterface::EVENT_THROW_TOKEN_ARRIVES,
            SignalEventDefinitionInterface::EVENT_THROW_EVENT_DEFINITION,
            BoundaryEventInterface::EVENT_BOUNDARY_EVENT_CATCH,
            BoundaryEventInterface::EVENT_BOUNDARY_EVENT_CONSUMED,

            EndEventInterface::EVENT_THROW_TOKEN_CONSUMED,
            EndEventInterface::EVENT_EVENT_TRIGGERED,
            ActivityInterface::EVENT_ACTIVITY_ACTIVATED,
        ]);

        // Assertion: Task 1 should keep its token
        $this->assertEquals(1, $task1->getTokens($instance)->count());

        // Assertion: Task 3 has one token
        $this->assertEquals(1, $task3->getTokens($instance)->count());

        // Complete first task
        $task1->complete($task1->getTokens($instance)->item(0));
        $this->engine->runToNextState();

        // Assertion: Task 2 is completed, an end event Signal is thrown, then caught by the boundary event
        $this->assertEvents([
            ActivityInterface::EVENT_ACTIVITY_COMPLETED,
            ActivityInterface::EVENT_ACTIVITY_CLOSED,
            ActivityInterface::EVENT_ACTIVITY_ACTIVATED,
        ]);

        // Assertion: Task 1 complete its token
        $this->assertEquals(0, $task1->getTokens($instance)->count());

        // Assertion: Task 4 has one token
        $this->assertEquals(1, $task4->getTokens($instance)->count());
    }

    /**
     * Tests a process with a cycle timer boundary non interrupting event attached to a task
     */
    public function testCycleTimerBoundaryEventNonInterrupting()
    {
        // Load the process from a BPMN file
        $bpmnRepository = new BpmnDocument();
        $bpmnRepository->setEngine($this->engine);
        $bpmnRepository->setFactory($this->repository);
        $bpmnRepository->load(__DIR__ . '/files/Timer_BoundaryEvent_Cycle_NonInterrupting.bpmn');

        // Get the process by Id
        $process = $bpmnRepository->getProcess('PROCESS_1');
        $dataStore = $this->repository->createDataStore();

        // Create an instance of the process
        $instance = $this->engine->createExecutionInstance($process, $dataStore);

        // Get References
        $start = $bpmnRepository->getStartEvent('_2');
        $task1 = $bpmnRepository->getActivity('_5');
        $task2 = $bpmnRepository->getActivity('_12');
        $boundaryEvent = $bpmnRepository->getBoundaryEvent('_11');

        // Start a process instance
        $start->start($instance);
        $this->engine->runToNextState();

        // Assertion: The process is started, a task is activated and a timer event scheduled
        $this->assertEvents([
            ProcessInterface::EVENT_PROCESS_INSTANCE_CREATED,
            EventInterface::EVENT_EVENT_TRIGGERED,
            ActivityInterface::EVENT_ACTIVITY_ACTIVATED,
            JobManagerInterface::EVENT_SCHEDULE_CYCLE,
        ]);

        // Boundary event is attached to $task1, its token is associated with the timer event
        $activeToken = $task1->getTokens($instance)->item(0);

        // Assertion: The jobs manager receive a scheduling request to trigger the start event time cycle specified in the process
        $this->assertScheduledCyclicTimer(new DatePeriod('R4/2018-05-01T00:00:00Z/PT1M'), $boundaryEvent, $activeToken);

        // Trigger the boundary event
        $boundaryEvent->execute($boundaryEvent->getEventDefinitions()->item(0), $instance);
        $this->engine->runToNextState();

        // Assertion: Boundary event was caught, and one token is placed in task 2
        $this->assertEvents([
            BoundaryEventInterface::EVENT_BOUNDARY_EVENT_CATCH,
            BoundaryEventInterface::EVENT_BOUNDARY_EVENT_CONSUMED,

            ActivityInterface::EVENT_ACTIVITY_ACTIVATED,
        ]);

        // Trigger the boundary event
        $boundaryEvent->execute($boundaryEvent->getEventDefinitions()->item(0), $instance);
        $this->engine->runToNextState();

        // Assertion: Boundary event was caught again, and another token is placed in task 2
        $this->assertEvents([
            BoundaryEventInterface::EVENT_BOUNDARY_EVENT_CATCH,
            BoundaryEventInterface::EVENT_BOUNDARY_EVENT_CONSUMED,

            ActivityInterface::EVENT_ACTIVITY_ACTIVATED,
        ]);

        // Assertion: Task 1 has one token and task 2 has two tokens
        $this->assertEquals(1, $task1->getTokens($instance)->count());
        $this->assertEquals(2, $task2->getTokens($instance)->count());

        // Complete second task
        $task1->complete($task1->getTokens($instance)->item(0));
        $this->engine->runToNextState();

        // Complete second task
        $task2->complete($task2->getTokens($instance)->item(1));
        $this->engine->runToNextState();

        $task2->complete($task2->getTokens($instance)->item(0));
        $this->engine->runToNextState();

        // Assertion: Three tokens completed
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

            ActivityInterface::EVENT_ACTIVITY_COMPLETED,
            ActivityInterface::EVENT_ACTIVITY_CLOSED,
            EndEventInterface::EVENT_THROW_TOKEN_ARRIVES,
            EndEventInterface::EVENT_THROW_TOKEN_CONSUMED,
            EndEventInterface::EVENT_EVENT_TRIGGERED,

            ProcessInterface::EVENT_PROCESS_INSTANCE_COMPLETED,
        ]);
    }

    /**
     * Tests a process with an error boundary non interrupting event attached to a script task
     */
    public function testErrorBoundaryEventScriptTaskNonInterrupting()
    {
        // Load the process from a BPMN file
        $bpmnRepository = new BpmnDocument();
        $bpmnRepository->setEngine($this->engine);
        $bpmnRepository->setFactory($this->repository);
        $bpmnRepository->load(__DIR__ . '/files/Error_BoundaryEvent_ScriptTask_NonInterrupting.bpmn');

        // Get the process by Id
        $process = $bpmnRepository->getProcess('PROCESS_1');
        $dataStore = $this->repository->createDataStore();

        // Create an instance of the process
        $instance = $this->engine->createExecutionInstance($process, $dataStore);

        // Get References
        $start = $bpmnRepository->getStartEvent('_2');
        $task1 = $bpmnRepository->getScriptTask('_5');
        $task2 = $bpmnRepository->getActivity('_12');

        // Start a process instance
        $start->start($instance);
        $this->engine->runToNextState();

        // Assertion: The process is started and the script activated
        $this->assertEvents([
            ProcessInterface::EVENT_PROCESS_INSTANCE_CREATED,
            EventInterface::EVENT_EVENT_TRIGGERED,
            ActivityInterface::EVENT_ACTIVITY_ACTIVATED,
            ScriptTaskInterface::EVENT_SCRIPT_TASK_ACTIVATED,
        ]);

        // Run the script task
        $activeToken = $task1->getTokens($instance)->item(0);
        $task1->runScript($activeToken);
        $this->engine->runToNextState();

        // Assertion: Script task throws an exception
        $this->assertEvents([
            ScriptTaskInterface::EVENT_ACTIVITY_EXCEPTION,
            BoundaryEventInterface::EVENT_BOUNDARY_EVENT_CATCH,
            BoundaryEventInterface::EVENT_BOUNDARY_EVENT_CONSUMED,

            ActivityInterface::EVENT_ACTIVITY_ACTIVATED,
        ]);

        // Assertion: Task 2 is ACTIVE and Task 1 is in FAILING
        $this->assertEquals(1, $task2->getTokens($instance)->count());
        $this->assertEquals(ActivityInterface::TOKEN_STATE_ACTIVE, $task2->getTokens($instance)->item(0)->getStatus());
        $this->assertEquals(1, $task1->getTokens($instance)->count());
        $this->assertEquals(ActivityInterface::TOKEN_STATE_FAILING, $task1->getTokens($instance)->item(0)->getStatus());
    }

    /**
     * Tests a process with an error boundary non interrupting event attached to a CallActivity
     */
    public function testErrorBoundaryEventCallActivityNonInterrupting()
    {
        // Load the process from a BPMN file
        $bpmnRepository = new BpmnDocument();
        $bpmnRepository->setEngine($this->engine);
        $bpmnRepository->setFactory($this->repository);
        $bpmnRepository->load(__DIR__ . '/files/Error_BoundaryEvent_CallActivity_NonInterrupting.bpmn');

        // Get the process by Id
        $process = $bpmnRepository->getProcess('PROCESS_1');
        $dataStore = $this->repository->createDataStore();

        // Create an instance of the process
        $instance = $this->engine->createExecutionInstance($process, $dataStore);

        // Get References
        $start = $bpmnRepository->getStartEvent('_4');
        $task1 = $bpmnRepository->getCallActivity('_7');
        $task2 = $bpmnRepository->getActivity('_13');

        // Start a process instance
        $start->start($instance);
        $this->engine->runToNextState();

        // Assertion: The process is started, then the sub process throw and ErrorEvent, catch by the BoundaryEvent
        $this->assertEvents([
            ProcessInterface::EVENT_PROCESS_INSTANCE_CREATED,
            EventInterface::EVENT_EVENT_TRIGGERED,
            ActivityInterface::EVENT_ACTIVITY_ACTIVATED,

            ProcessInterface::EVENT_PROCESS_INSTANCE_CREATED,
            EndEventInterface::EVENT_EVENT_TRIGGERED,
            EndEventInterface::EVENT_THROW_TOKEN_ARRIVES,
            ErrorEventDefinition::EVENT_THROW_EVENT_DEFINITION,
            EndEventInterface::EVENT_THROW_TOKEN_CONSUMED,
            EndEventInterface::EVENT_EVENT_TRIGGERED,
            ProcessInterface::EVENT_PROCESS_INSTANCE_COMPLETED,
            ActivityInterface::EVENT_ACTIVITY_EXCEPTION,
            BoundaryEventInterface::EVENT_BOUNDARY_EVENT_CATCH,

            BoundaryEventInterface::EVENT_BOUNDARY_EVENT_CONSUMED,
            ActivityInterface::EVENT_ACTIVITY_ACTIVATED,
        ]);

        // Assertion: Task 2 is ACTIVE and CallActivity is in FAILING
        $this->assertEquals(1, $task2->getTokens($instance)->count());
        $this->assertEquals(ActivityInterface::TOKEN_STATE_ACTIVE, $task2->getTokens($instance)->item(0)->getStatus());
        $this->assertEquals(1, $task1->getTokens($instance)->count());
        $this->assertEquals(ActivityInterface::TOKEN_STATE_FAILING, $task1->getTokens($instance)->item(0)->getStatus());
    }

    /**
     * Tests a process with a cycle timer boundary non interrupting event attached to a CallActivity
     */
    public function testCycleTimerBoundaryEventCallActivityNonInterrupting()
    {
        // Load the process from a BPMN file
        $bpmnRepository = new BpmnDocument();
        $bpmnRepository->setEngine($this->engine);
        $bpmnRepository->setFactory($this->repository);
        $bpmnRepository->load(__DIR__ . '/files/Timer_BoundaryEvent_CallActivity_NonInterrupting.bpmn');

        // Get the process by Id
        $process = $bpmnRepository->getProcess('PROCESS_1');
        $subProcess = $bpmnRepository->getProcess('PROCESS_2');
        $dataStore = $this->repository->createDataStore();

        // Create an instance of the process
        $instance = $this->engine->createExecutionInstance($process, $dataStore);

        // Get References
        $start = $bpmnRepository->getStartEvent('_4');
        $task1 = $bpmnRepository->getCallActivity('_7');
        $subTask = $bpmnRepository->getActivity('_5');
        $boundaryEvent = $bpmnRepository->getBoundaryEvent('_12');

        // Start a process instance
        $start->start($instance);
        $this->engine->runToNextState();

        // Assertion: The process is started, a task is activated, the sub process is started and a timer event scheduled
        $this->assertEvents([
            ProcessInterface::EVENT_PROCESS_INSTANCE_CREATED,
            EventInterface::EVENT_EVENT_TRIGGERED,
            ActivityInterface::EVENT_ACTIVITY_ACTIVATED,
            ProcessInterface::EVENT_PROCESS_INSTANCE_CREATED,
            JobManagerInterface::EVENT_SCHEDULE_CYCLE,
            StartEventInterface::EVENT_EVENT_TRIGGERED,
            ActivityInterface::EVENT_ACTIVITY_ACTIVATED,
        ]);

        // Boundary event is attached to $task1, its token is associated with the timer event
        $activeToken = $task1->getTokens($instance)->item(0);

        // Assertion: The jobs manager receive a scheduling request to trigger the start event time cycle specified in the process
        $this->assertScheduledCyclicTimer(new DatePeriod('R4/2018-05-01T00:00:00Z/PT1M'), $boundaryEvent, $activeToken);

        // Trigger the boundary event
        $boundaryEvent->execute($boundaryEvent->getEventDefinitions()->item(0), $instance);
        $this->engine->runToNextState();

        // Assertion: Boundary event was caught, activate Task 2
        $this->assertEvents([
            BoundaryEventInterface::EVENT_BOUNDARY_EVENT_CATCH,
            BoundaryEventInterface::EVENT_BOUNDARY_EVENT_CONSUMED,
            ActivityInterface::EVENT_ACTIVITY_ACTIVATED,
        ]);

        // Assertion: Call activity and subprocess keep its tokens
        $this->assertEquals(1, $task1->getTokens($instance)->count());
        $this->assertEquals(ActivityInterface::TOKEN_STATE_ACTIVE, $task1->getTokens($instance)->item(0)->getStatus());

        $subInstance = $subProcess->getInstances()->item(0);
        $this->assertEquals(1, $subTask->getTokens($subInstance)->count());
        $this->assertEquals(ActivityInterface::TOKEN_STATE_ACTIVE, $subTask->getTokens($subInstance)->item(0)->getStatus());
    }

    /**
     * Tests the a process with a signal boundary non interrupting event attached to a CallActivity
     */
    public function testSignalBoundaryEventCallActivityNonInterrupting()
    {
        // Load the process from a BPMN file
        $bpmnRepository = new BpmnDocument();
        $bpmnRepository->setEngine($this->engine);
        $bpmnRepository->setFactory($this->repository);
        $bpmnRepository->load(__DIR__ . '/files/Signal_BoundaryEvent_CallActivity_NonInterrupting.bpmn');

        // Get the process by Id
        $process = $bpmnRepository->getProcess('PROCESS_1');
        $subProcess = $bpmnRepository->getProcess('PROCESS_2');
        $dataStore = $this->repository->createDataStore();

        // Create an instance of the process
        $instance = $this->engine->createExecutionInstance($process, $dataStore);

        // Get References
        $start = $bpmnRepository->getStartEvent('_4');
        $task1 = $bpmnRepository->getCallActivity('_7');
        $subTask = $bpmnRepository->getActivity('_5');

        // Start a process instance
        $start->start($instance);
        $this->engine->runToNextState();

        // Assertion: The process is started and two tasks were activated
        $this->assertEvents([
            ProcessInterface::EVENT_PROCESS_INSTANCE_CREATED,
            EventInterface::EVENT_EVENT_TRIGGERED,
            ActivityInterface::EVENT_ACTIVITY_ACTIVATED,
            ProcessInterface::EVENT_PROCESS_INSTANCE_CREATED,
            StartEventInterface::EVENT_EVENT_TRIGGERED,
            BoundaryEventInterface::EVENT_BOUNDARY_EVENT_CATCH,
            IntermediateThrowEventInterface::EVENT_THROW_TOKEN_ARRIVES,
            IntermediateThrowEventInterface::EVENT_THROW_TOKEN_CONSUMED,
            IntermediateThrowEventInterface::EVENT_THROW_TOKEN_PASSED,
            ActivityInterface::EVENT_ACTIVITY_ACTIVATED,
            BoundaryEventInterface::EVENT_BOUNDARY_EVENT_CONSUMED,
            ActivityInterface::EVENT_ACTIVITY_ACTIVATED,
        ]);

        // Assertion: Call activity and subprocess keep its tokens
        $this->assertEquals(1, $task1->getTokens($instance)->count());
        $this->assertEquals(ActivityInterface::TOKEN_STATE_ACTIVE, $task1->getTokens($instance)->item(0)->getStatus());

        $subInstance = $subProcess->getInstances()->item(0);
        $this->assertEquals(1, $subTask->getTokens($subInstance)->count());
        $this->assertEquals(ActivityInterface::TOKEN_STATE_ACTIVE, $subTask->getTokens($subInstance)->item(0)->getStatus());
    }

    /**
     * Tests a process with concurrent boundary events attached to a CallActivity
     */
    public function testConcurrentBoundaryEventCallActivity()
    {
        // Load the process from a BPMN file
        $bpmnRepository = new BpmnDocument();
        $bpmnRepository->setEngine($this->engine);
        $bpmnRepository->setFactory($this->repository);
        $bpmnRepository->load(__DIR__ . '/files/Concurrent_BoundaryEvent_CallActivity.bpmn');

        // Get the process by Id
        $process = $bpmnRepository->getProcess('PROCESS_1');
        $subProcess = $bpmnRepository->getProcess('PROCESS_2');
        $dataStore = $this->repository->createDataStore();

        // Create an instance of the process
        $instance = $this->engine->createExecutionInstance($process, $dataStore);

        // Get References
        $start = $bpmnRepository->getStartEvent('_4');
        $task1 = $bpmnRepository->getCallActivity('_7');
        $task2 = $bpmnRepository->getActivity('_13');
        $task3 = $bpmnRepository->getActivity('_23');
        $task4 = $bpmnRepository->getActivity('_25');
        $task5 = $bpmnRepository->getActivity('_5');
        $timer1BoundaryEvent = $bpmnRepository->getBoundaryEvent('_21');
        $timer2BoundaryEvent = $bpmnRepository->getBoundaryEvent('_22');
        $timer3Intermediate = $bpmnRepository->getBoundaryEvent('_19');

        // Start a process instance
        $start->start($instance);
        $this->engine->runToNextState();

        // Get active tokens (Task 1, IntermediateTimerEvent)
        $activeToken = $task1->getTokens($instance)->item(0);
        $subInstance = $subProcess->getInstances()->item(0);
        $subTimerToken = $timer3Intermediate->getTokens($subInstance)->item(0);

        // Assertion: Three timer events should be scheduled (timer1BoundaryEvent, timer2BoundaryEvent, timer3Intermediate)
        $this->assertScheduledCyclicTimer(new DatePeriod('R4/2018-05-01T00:00:00Z/PT1M'), $timer1BoundaryEvent, $activeToken);
        $this->assertScheduledCyclicTimer(new DatePeriod('R4/2018-05-01T00:00:00Z/PT2M'), $timer2BoundaryEvent, $activeToken);
        $this->assertScheduledCyclicTimer(new DatePeriod('R4/2018-05-01T00:00:00Z/PT4M'), $timer3Intermediate, $subTimerToken);

        // Trigger the three timer events at the same time
        $timer1BoundaryEvent->execute($timer1BoundaryEvent->getEventDefinitions()->item(0), $instance);
        $timer2BoundaryEvent->execute($timer2BoundaryEvent->getEventDefinitions()->item(0), $instance);
        $timer3Intermediate->execute($timer3Intermediate->getEventDefinitions()->item(0), $subInstance);
        $this->engine->runToNextState();

        // foreach($instance->getTokens() as $token) var_dump($token->getOwnerElement()->getName() . '=' . $token->getStatus());
        // foreach($subInstance->getTokens() as $token) var_dump($token->getOwnerElement()->getName() . '=' . $token->getStatus());

        $this->assertEvents([
            // Assertion: Main process is started
            ProcessInterface::EVENT_PROCESS_INSTANCE_CREATED,
            StartEventInterface::EVENT_EVENT_TRIGGERED,

            // Assertion: SubProcess is started
            ActivityInterface::EVENT_ACTIVITY_ACTIVATED,
            ProcessInterface::EVENT_PROCESS_INSTANCE_CREATED,

            // Assertion: Timers are scheduled
            JobManagerInterface::EVENT_SCHEDULE_CYCLE,
            JobManagerInterface::EVENT_SCHEDULE_CYCLE,
            StartEventInterface::EVENT_EVENT_TRIGGERED,
            IntermediateCatchEventInterface::EVENT_CATCH_TOKEN_ARRIVES,
            JobManagerInterface::EVENT_SCHEDULE_CYCLE,

            // Assertion: Timer events attached to Task 1 and in the Sub Process, are catch
            BoundaryEventInterface::EVENT_BOUNDARY_EVENT_CATCH,
            BoundaryEventInterface::EVENT_BOUNDARY_EVENT_CATCH,
            IntermediateCatchEventInterface::EVENT_CATCH_MESSAGE_CATCH,
            BoundaryEventInterface::EVENT_BOUNDARY_EVENT_CONSUMED,
            BoundaryEventInterface::EVENT_BOUNDARY_EVENT_CONSUMED,

            // Assertion: Call Activity is cancelled
            ActivityInterface::EVENT_ACTIVITY_CANCELLED,

            // Assertion: Intermediate event is triggered
            IntermediateCatchEventInterface::EVENT_CATCH_TOKEN_CONSUMED,
            IntermediateCatchEventInterface::EVENT_CATCH_MESSAGE_CONSUMED,
            IntermediateCatchEventInterface::EVENT_CATCH_TOKEN_PASSED,
            IntermediateThrowEventInterface::EVENT_THROW_TOKEN_ARRIVES,
            IntermediateThrowEventInterface::EVENT_EVENT_TRIGGERED,

            // Assertion: Task 3 and  Task 4 are activated
            ActivityInterface::EVENT_ACTIVITY_ACTIVATED,
            ActivityInterface::EVENT_ACTIVITY_ACTIVATED,

            // // Assertion: Interrupting BoundaryTimerEvent move token to the next task
            // BoundaryEventInterface::EVENT_BOUNDARY_EVENT_CATCH,
            // BoundaryEventInterface::EVENT_BOUNDARY_EVENT_CONSUMED,
            //
            // // Assertion: Task 2 is activated by BoundarySignalEvent
            // ActivityInterface::EVENT_ACTIVITY_ACTIVATED,

            // Assertion: ThrowSignal element is completed and goes to next task 5
            IntermediateThrowEventInterface::EVENT_THROW_TOKEN_CONSUMED,
            IntermediateThrowEventInterface::EVENT_THROW_TOKEN_PASSED,
            ActivityInterface::EVENT_ACTIVITY_ACTIVATED,
            //
            // ActivityInterface::EVENT_ACTIVITY_CANCELLED,
            // ProcessInterface::EVENT_PROCESS_INSTANCE_COMPLETED,
        ]);

        // Assertion: Task3, Task 4 are ACTIVE and Task 1 is closed
        $this->assertEquals(1, $task3->getTokens($instance)->count());
        $this->assertEquals(1, $task4->getTokens($instance)->count());
        $this->assertEquals(1, $task5->getTokens($subInstance)->count());
        $this->assertEquals(0, $task1->getTokens($instance)->count());
    }

    /**
     * Tests a process with concurrent non interrupting boundary events attached to a CallActivity
     */
    public function testConcurrentBoundaryEventCallActivityNonInterrupting()
    {
        // Load the process from a BPMN file
        $bpmnRepository = new BpmnDocument();
        $bpmnRepository->setEngine($this->engine);
        $bpmnRepository->setFactory($this->repository);
        $bpmnRepository->load(__DIR__ . '/files/Concurrent_BoundaryEvent_CallActivity_NonInterrupting.bpmn');

        // Get the process by Id
        $process = $bpmnRepository->getProcess('PROCESS_1');
        $subProcess = $bpmnRepository->getProcess('PROCESS_2');
        $dataStore = $this->repository->createDataStore();

        // Create an instance of the process
        $instance = $this->engine->createExecutionInstance($process, $dataStore);

        // Get References
        $start = $bpmnRepository->getStartEvent('_4');
        $task1 = $bpmnRepository->getCallActivity('_7');
        $task2 = $bpmnRepository->getActivity('_13');
        $task3 = $bpmnRepository->getActivity('_23');
        $task4 = $bpmnRepository->getActivity('_25');
        $timer1BoundaryEvent = $bpmnRepository->getBoundaryEvent('_21');
        $timer2BoundaryEvent = $bpmnRepository->getBoundaryEvent('_22');
        $timer3Intermediate = $bpmnRepository->getBoundaryEvent('_19');

        // Start a process instance
        $start->start($instance);
        $this->engine->runToNextState();

        // Get active tokens (Task 1, IntermediateTimerEvent)
        $activeToken = $task1->getTokens($instance)->item(0);
        $subInstance = $subProcess->getInstances()->item(0);
        $subTimerToken = $timer3Intermediate->getTokens($subInstance)->item(0);

        // Assertion: Three timer events should be scheduled (timer1BoundaryEvent, timer2BoundaryEvent, timer3Intermediate)
        $this->assertScheduledCyclicTimer(new DatePeriod('R4/2018-05-01T00:00:00Z/PT1M'), $timer1BoundaryEvent, $activeToken);
        $this->assertScheduledCyclicTimer(new DatePeriod('R4/2018-05-01T00:00:00Z/PT2M'), $timer2BoundaryEvent, $activeToken);
        $this->assertScheduledCyclicTimer(new DatePeriod('R4/2018-05-01T00:00:00Z/PT4M'), $timer3Intermediate, $subTimerToken);

        // Trigger the three timer events at the same time
        $timer1BoundaryEvent->execute($timer1BoundaryEvent->getEventDefinitions()->item(0), $instance);
        $timer2BoundaryEvent->execute($timer2BoundaryEvent->getEventDefinitions()->item(0), $instance);
        $timer3Intermediate->execute($timer3Intermediate->getEventDefinitions()->item(0), $subInstance);
        $this->engine->runToNextState();

        $this->assertEvents([
            // Assertion: Main process is started
            ProcessInterface::EVENT_PROCESS_INSTANCE_CREATED,
            StartEventInterface::EVENT_EVENT_TRIGGERED,

            // Assertion: SubProcess is started
            ActivityInterface::EVENT_ACTIVITY_ACTIVATED,
            ProcessInterface::EVENT_PROCESS_INSTANCE_CREATED,

            // Assertion: Timers are scheduled
            JobManagerInterface::EVENT_SCHEDULE_CYCLE,
            JobManagerInterface::EVENT_SCHEDULE_CYCLE,
            StartEventInterface::EVENT_EVENT_TRIGGERED,
            IntermediateCatchEventInterface::EVENT_CATCH_TOKEN_ARRIVES,
            JobManagerInterface::EVENT_SCHEDULE_CYCLE,

            // Assertion: Timer events attached to Task 1 and in the Sub Process, are catch
            BoundaryEventInterface::EVENT_BOUNDARY_EVENT_CATCH,
            BoundaryEventInterface::EVENT_BOUNDARY_EVENT_CATCH,
            IntermediateCatchEventInterface::EVENT_CATCH_MESSAGE_CATCH,
            BoundaryEventInterface::EVENT_BOUNDARY_EVENT_CONSUMED,
            BoundaryEventInterface::EVENT_BOUNDARY_EVENT_CONSUMED,

            // Assertion: IntermediateCatchEvent completed
            IntermediateCatchEventInterface::EVENT_CATCH_TOKEN_CONSUMED,
            IntermediateCatchEventInterface::EVENT_CATCH_MESSAGE_CONSUMED,
            IntermediateCatchEventInterface::EVENT_CATCH_TOKEN_PASSED,

            // Assertion: BoundarySignalEvent catch signal from ThrowSignal
            BoundaryEventInterface::EVENT_BOUNDARY_EVENT_CATCH,
            IntermediateThrowEventInterface::EVENT_THROW_TOKEN_ARRIVES,
            IntermediateThrowEventInterface::EVENT_EVENT_TRIGGERED,
            BoundaryEventInterface::EVENT_BOUNDARY_EVENT_CONSUMED,

            // Assertion: Task 3 and  Task 4 are activated
            ActivityInterface::EVENT_ACTIVITY_ACTIVATED,
            ActivityInterface::EVENT_ACTIVITY_ACTIVATED,

            // Assertion: ThrowSignal element is completed and goes to next task 5
            IntermediateThrowEventInterface::EVENT_THROW_TOKEN_CONSUMED,
            IntermediateThrowEventInterface::EVENT_THROW_TOKEN_PASSED,
            ActivityInterface::EVENT_ACTIVITY_ACTIVATED,

            // Assertion: Task 2 is activated by BoundarySignalEvent
            ActivityInterface::EVENT_ACTIVITY_ACTIVATED,
        ]);

        // Assertion: Task 2, Task3, Task 4 and Task 1 are ACTIVE
        $this->assertEquals(1, $task2->getTokens($instance)->count());
        $this->assertEquals(1, $task3->getTokens($instance)->count());
        $this->assertEquals(1, $task4->getTokens($instance)->count());
        $this->assertEquals(1, $task1->getTokens($instance)->count());
    }
}
