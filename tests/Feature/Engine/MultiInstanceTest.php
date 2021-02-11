<?php

namespace Tests\Feature\Engine;

use ProcessMaker\Nayra\Contracts\Bpmn\ActivityInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\EventInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\ProcessInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\ScriptTaskInterface;
use ProcessMaker\Nayra\Storage\BpmnDocument;

/**
 * Test a terminate event.
 *
 */
class MultiInstanceTest extends EngineTestCase
{

    /**
     * Test a parallel multiinstance
     *
     */
    public function testMultiInstanceParallelLoopCardinality()
    {
        // Load a BpmnFile Repository
        $bpmnRepository = new BpmnDocument();
        $bpmnRepository->setEngine($this->engine);
        $bpmnRepository->setFactory($this->repository);
        $bpmnRepository->load(__DIR__ . '/../Patterns/files/MultiInstance_Parallel.bpmn');

        // Load a process from a bpmn repository by Id
        $process = $bpmnRepository->getProcess('MultiInstance_Parallel');

        // Get 'start' activity of the process
        $activity = $bpmnRepository->getActivity('start');

        // Get 'MultiInstanceTask' activity of the process
        $miTask = $bpmnRepository->getActivity('MultiInstanceTask');

        // Start the process
        $instance = $process->call();
        $this->engine->runToNextState();

        // Assertion: A process has started.
        $this->assertEquals(1, $process->getInstances()->count());

        // Assertion: The process has started and the first activity was actived
        $this->assertEvents([
            ProcessInterface::EVENT_PROCESS_INSTANCE_CREATED,
            EventInterface::EVENT_EVENT_TRIGGERED,
            ActivityInterface::EVENT_ACTIVITY_ACTIVATED,
            ScriptTaskInterface::EVENT_SCRIPT_TASK_ACTIVATED,
        ]);

        // Complete the first activity.
        $token = $activity->getTokens($instance)->item(0);
        $activity->complete($token);
        $this->engine->runToNextState();

        // Assertion: The first activity was completed then 3 script task are activated
        $this->assertEvents([
            ActivityInterface::EVENT_ACTIVITY_COMPLETED,
            ActivityInterface::EVENT_ACTIVITY_CLOSED,

            ActivityInterface::EVENT_ACTIVITY_ACTIVATED,
            ScriptTaskInterface::EVENT_SCRIPT_TASK_ACTIVATED,
            ActivityInterface::EVENT_ACTIVITY_ACTIVATED,
            ScriptTaskInterface::EVENT_SCRIPT_TASK_ACTIVATED,
            ActivityInterface::EVENT_ACTIVITY_ACTIVATED,
            ScriptTaskInterface::EVENT_SCRIPT_TASK_ACTIVATED,
        ]);

        // Complete the first MI activity.
        $token = $miTask->getTokens($instance)->item(0);
        $activity->complete($token);
        $this->engine->runToNextState();

        // Assertion: The first MI task was completed 2 pending
        $this->assertEvents([
            ActivityInterface::EVENT_ACTIVITY_COMPLETED,
        ]);

        // Complete the second MI activity.
        $token = $miTask->getTokens($instance)->item(0);
        $activity->complete($token);
        $this->engine->runToNextState();

        // Assertion: The second MI task was completed 1 pending
        $this->assertEvents([
            ActivityInterface::EVENT_ACTIVITY_COMPLETED,
        ]);

        // Complete the third MI activity.
        $token = $miTask->getTokens($instance)->item(0);
        $activity->complete($token);
        $this->engine->runToNextState();

        // Assertion: The thrid MI task was completed, all MI token are closed, then continue to next task
        $this->assertEvents([
            ActivityInterface::EVENT_ACTIVITY_COMPLETED,
            ActivityInterface::EVENT_ACTIVITY_CLOSED,
            ActivityInterface::EVENT_ACTIVITY_CLOSED,
            ActivityInterface::EVENT_ACTIVITY_CLOSED,

            ActivityInterface::EVENT_ACTIVITY_ACTIVATED,
            ScriptTaskInterface::EVENT_SCRIPT_TASK_ACTIVATED,
        ]);
    }

    /**
     * Test MI script task with exception
     *
     */
    public function testMultiInstanceParallelLoopCardinalityScriptException()
    {
        // Load a BpmnFile Repository
        $bpmnRepository = new BpmnDocument();
        $bpmnRepository->setEngine($this->engine);
        $bpmnRepository->setFactory($this->repository);
        $bpmnRepository->load(__DIR__ . '/../Patterns/files/MultiInstance_Parallel.bpmn');

        // Load a process from a bpmn repository by Id
        $process = $bpmnRepository->getProcess('MultiInstance_Parallel');

        // Get 'start' activity of the process
        $activity = $bpmnRepository->getActivity('start');

        // Get 'MultiInstanceTask' activity of the process
        $miTask = $bpmnRepository->getActivity('MultiInstanceTask');

        // Start the process
        $instance = $process->call();
        $this->engine->runToNextState();

        // Assertion: A process has started.
        $this->assertEquals(1, $process->getInstances()->count());

        // Assertion: The process has started and the first activity was actived
        $this->assertEvents([
            ProcessInterface::EVENT_PROCESS_INSTANCE_CREATED,
            EventInterface::EVENT_EVENT_TRIGGERED,
            ActivityInterface::EVENT_ACTIVITY_ACTIVATED,
            ScriptTaskInterface::EVENT_SCRIPT_TASK_ACTIVATED,
        ]);

        // Complete the first activity.
        $token = $activity->getTokens($instance)->item(0);
        $activity->complete($token);
        $this->engine->runToNextState();

        // Assertion: The first activity was completed then 3 script task are activated
        $this->assertEvents([
            ActivityInterface::EVENT_ACTIVITY_COMPLETED,
            ActivityInterface::EVENT_ACTIVITY_CLOSED,

            ActivityInterface::EVENT_ACTIVITY_ACTIVATED,
            ScriptTaskInterface::EVENT_SCRIPT_TASK_ACTIVATED,
            ActivityInterface::EVENT_ACTIVITY_ACTIVATED,
            ScriptTaskInterface::EVENT_SCRIPT_TASK_ACTIVATED,
            ActivityInterface::EVENT_ACTIVITY_ACTIVATED,
            ScriptTaskInterface::EVENT_SCRIPT_TASK_ACTIVATED,
        ]);

        // Complete the first MI activity.
        $token = $miTask->getTokens($instance)->item(0);
        $activity->complete($token);
        $this->engine->runToNextState();

        // Assertion: The first MI task was completed 2 pending
        $this->assertEvents([
            ActivityInterface::EVENT_ACTIVITY_COMPLETED,
        ]);

        // Complete the second MI activity.
        $token = $miTask->getTokens($instance)->item(0);
        $activity->complete($token);
        $this->engine->runToNextState();

        // Assertion: The second MI task was completed 1 pending
        $this->assertEvents([
            ActivityInterface::EVENT_ACTIVITY_COMPLETED,
        ]);

        // Complete the third MI activity.
        $token = $miTask->getTokens($instance)->item(0);
        $token->setStatus(ScriptTaskInterface::TOKEN_STATE_FAILING);
        $this->engine->runToNextState();

        // Assertion: Fail thrid MI task
        $this->assertEvents([
            ActivityInterface::EVENT_ACTIVITY_EXCEPTION,
        ]);

        // Close failing task instance.
        $token->setStatus(ScriptTaskInterface::TOKEN_STATE_CLOSED);
        $this->engine->runToNextState();

        // Assertion: The thrid MI task was cancelled, the MI Activity hangs because it could not close all the parallel instances
        $this->assertEvents([
            ActivityInterface::EVENT_ACTIVITY_CANCELLED,
        ]);
    }

    /**
     * Test MI task cancel instance
     *
     */
    public function testMultiInstanceParallelLoopCardinalityCancellInstance()
    {
        // Load a BpmnFile Repository
        $bpmnRepository = new BpmnDocument();
        $bpmnRepository->setEngine($this->engine);
        $bpmnRepository->setFactory($this->repository);
        $bpmnRepository->load(__DIR__ . '/../Patterns/files/MultiInstance_Parallel.bpmn');

        // Load a process from a bpmn repository by Id
        $process = $bpmnRepository->getProcess('MultiInstance_Parallel');

        // Get 'start' activity of the process
        $activity = $bpmnRepository->getActivity('start');

        // Get 'multiInstanceTask' activity of the process
        $miTask = $bpmnRepository->getActivity('MultiInstanceTask');

        // Start the process
        $instance = $process->call();
        $this->engine->runToNextState();

        // Assertion: A process has started.
        $this->assertEquals(1, $process->getInstances()->count());

        // Assertion: The process has started and the first activity was actived
        $this->assertEvents([
            ProcessInterface::EVENT_PROCESS_INSTANCE_CREATED,
            EventInterface::EVENT_EVENT_TRIGGERED,
            ActivityInterface::EVENT_ACTIVITY_ACTIVATED,
            ScriptTaskInterface::EVENT_SCRIPT_TASK_ACTIVATED,
        ]);

        // Complete the first activity.
        $token = $activity->getTokens($instance)->item(0);
        $activity->complete($token);
        $this->engine->runToNextState();

        // Assertion: The first activity was completed then 3 script task are activated
        $this->assertEvents([
            ActivityInterface::EVENT_ACTIVITY_COMPLETED,
            ActivityInterface::EVENT_ACTIVITY_CLOSED,

            ActivityInterface::EVENT_ACTIVITY_ACTIVATED,
            ScriptTaskInterface::EVENT_SCRIPT_TASK_ACTIVATED,
            ActivityInterface::EVENT_ACTIVITY_ACTIVATED,
            ScriptTaskInterface::EVENT_SCRIPT_TASK_ACTIVATED,
            ActivityInterface::EVENT_ACTIVITY_ACTIVATED,
            ScriptTaskInterface::EVENT_SCRIPT_TASK_ACTIVATED,
        ]);

        // Complete the first MI activity.
        $token = $miTask->getTokens($instance)->item(0);
        $activity->complete($token);
        $this->engine->runToNextState();

        // Assertion: The first MI task was completed 2 pending
        $this->assertEvents([
            ActivityInterface::EVENT_ACTIVITY_COMPLETED,
        ]);

        // Complete the second MI activity.
        $token = $miTask->getTokens($instance)->item(0);
        $activity->complete($token);
        $this->engine->runToNextState();

        // Assertion: The second MI task was completed 1 pending
        $this->assertEvents([
            ActivityInterface::EVENT_ACTIVITY_COMPLETED,
        ]);

        // Cancel the third MI activity.
        $token = $miTask->getTokens($instance)->item(0);
        $token->setStatus(ScriptTaskInterface::TOKEN_STATE_CLOSED);
        $this->engine->runToNextState();

        // Assertion: The thrid MI task was cancelled, the MI Activity hangs because it could not close all the parallel instances
        $this->assertEvents([
            ActivityInterface::EVENT_ACTIVITY_CANCELLED,
        ]);
    }

    /**
     * Test a sequential multiinstance
     *
     */
    public function testMultiInstanceSequentialLoopCardinality()
    {
        // Load a BpmnFile Repository
        $bpmnRepository = new BpmnDocument();
        $bpmnRepository->setEngine($this->engine);
        $bpmnRepository->setFactory($this->repository);
        $bpmnRepository->load(__DIR__ . '/../Patterns/files/MultiInstance_AllBehavior.bpmn');

        // Load a process from a bpmn repository by Id
        $process = $bpmnRepository->getProcess('MultiInstance_AllBehavior');

        // Get 'start' activity of the process
        $activity = $bpmnRepository->getActivity('start');

        // Get 'MultiInstanceTask' activity of the process
        $miTask = $bpmnRepository->getActivity('multiInstanceTask');

        // Start the process
        $instance = $process->call();
        $this->engine->runToNextState();

        // Assertion: A process has started.
        $this->assertEquals(1, $process->getInstances()->count());

        // Assertion: The process has started and the first activity was actived
        $this->assertEvents([
            ProcessInterface::EVENT_PROCESS_INSTANCE_CREATED,
            EventInterface::EVENT_EVENT_TRIGGERED,
            ActivityInterface::EVENT_ACTIVITY_ACTIVATED,
            ScriptTaskInterface::EVENT_SCRIPT_TASK_ACTIVATED,
        ]);

        // Complete the first activity.
        $token = $activity->getTokens($instance)->item(0);
        $activity->complete($token);
        $this->engine->runToNextState();

        // Assertion: The first activity was completed then 3 script task are activated
        $this->assertEvents([
            ActivityInterface::EVENT_ACTIVITY_COMPLETED,
            ActivityInterface::EVENT_ACTIVITY_CLOSED,

            ActivityInterface::EVENT_ACTIVITY_ACTIVATED,
            ScriptTaskInterface::EVENT_SCRIPT_TASK_ACTIVATED,
        ]);

        // Complete the first MI activity.
        $token = $miTask->getTokens($instance)->item(0);
        $activity->complete($token);
        $this->engine->runToNextState();

        // Assertion: The first MI task was completed 2 pending
        $this->assertEvents([
            ActivityInterface::EVENT_ACTIVITY_COMPLETED,
            ActivityInterface::EVENT_ACTIVITY_ACTIVATED,
            ScriptTaskInterface::EVENT_SCRIPT_TASK_ACTIVATED,
        ]);

        // Complete the second MI activity.
        $token = $miTask->getTokens($instance)->item(0);
        $activity->complete($token);
        $this->engine->runToNextState();

        // Assertion: The second MI task was completed 1 pending
        $this->assertEvents([
            ActivityInterface::EVENT_ACTIVITY_COMPLETED,
            ActivityInterface::EVENT_ACTIVITY_ACTIVATED,
            ScriptTaskInterface::EVENT_SCRIPT_TASK_ACTIVATED,
        ]);

        // Complete the third MI activity.
        $token = $miTask->getTokens($instance)->item(0);
        $activity->complete($token);
        $this->engine->runToNextState();

        // Assertion: The thrid MI task was completed, closed, then continue to next task
        $this->assertEvents([
            ActivityInterface::EVENT_ACTIVITY_COMPLETED,
            ActivityInterface::EVENT_ACTIVITY_CLOSED,

            ActivityInterface::EVENT_ACTIVITY_ACTIVATED,
            ScriptTaskInterface::EVENT_SCRIPT_TASK_ACTIVATED,
        ]);
    }

    /**
     * Test MI script task with exception
     *
     */
    public function testMultiInstanceSequentialLoopCardinalityScriptException()
    {
        // Load a BpmnFile Repository
        $bpmnRepository = new BpmnDocument();
        $bpmnRepository->setEngine($this->engine);
        $bpmnRepository->setFactory($this->repository);
        $bpmnRepository->load(__DIR__ . '/../Patterns/files/MultiInstance_AllBehavior.bpmn');

        // Load a process from a bpmn repository by Id
        $process = $bpmnRepository->getProcess('MultiInstance_AllBehavior');

        // Get 'start' activity of the process
        $activity = $bpmnRepository->getActivity('start');

        // Get 'multiInstanceTask' activity of the process
        $miTask = $bpmnRepository->getActivity('multiInstanceTask');

        // Start the process
        $instance = $process->call();
        $this->engine->runToNextState();

        // Assertion: A process has started.
        $this->assertEquals(1, $process->getInstances()->count());

        // Assertion: The process has started and the first activity was actived
        $this->assertEvents([
            ProcessInterface::EVENT_PROCESS_INSTANCE_CREATED,
            EventInterface::EVENT_EVENT_TRIGGERED,
            ActivityInterface::EVENT_ACTIVITY_ACTIVATED,
            ScriptTaskInterface::EVENT_SCRIPT_TASK_ACTIVATED,
        ]);

        // Complete the first activity.
        $token = $activity->getTokens($instance)->item(0);
        $activity->complete($token);
        $this->engine->runToNextState();

        // Assertion: The first activity was completed then 3 script task are activated
        $this->assertEvents([
            ActivityInterface::EVENT_ACTIVITY_COMPLETED,
            ActivityInterface::EVENT_ACTIVITY_CLOSED,

            ActivityInterface::EVENT_ACTIVITY_ACTIVATED,
            ScriptTaskInterface::EVENT_SCRIPT_TASK_ACTIVATED,
        ]);

        // Complete the first MI activity.
        $token = $miTask->getTokens($instance)->item(0);
        $activity->complete($token);
        $this->engine->runToNextState();

        // Assertion: The first MI task was completed 2 pending
        $this->assertEvents([
            ActivityInterface::EVENT_ACTIVITY_COMPLETED,
            ActivityInterface::EVENT_ACTIVITY_ACTIVATED,
            ScriptTaskInterface::EVENT_SCRIPT_TASK_ACTIVATED,
        ]);

        // Complete the second MI activity.
        $token = $miTask->getTokens($instance)->item(0);
        $activity->complete($token);
        $this->engine->runToNextState();

        // Assertion: The second MI task was completed 1 pending
        $this->assertEvents([
            ActivityInterface::EVENT_ACTIVITY_COMPLETED,
            ActivityInterface::EVENT_ACTIVITY_ACTIVATED,
            ScriptTaskInterface::EVENT_SCRIPT_TASK_ACTIVATED,
        ]);

        // Complete the third MI activity.
        $token = $miTask->getTokens($instance)->item(0);
        $token->setStatus(ScriptTaskInterface::TOKEN_STATE_FAILING);
        $this->engine->runToNextState();

        // Assertion: Fail thrid MI task, the MI Activity hangs until the failing instance is closed
        $this->assertEvents([
            ActivityInterface::EVENT_ACTIVITY_EXCEPTION,
        ]);

        // Close failing task instance.
        $token->setStatus(ScriptTaskInterface::TOKEN_STATE_CLOSED);
        $this->engine->runToNextState();

        // Assertion: The third and last MI Activity is cancelled, then the process is closed
        $this->assertEvents([
            ActivityInterface::EVENT_ACTIVITY_CANCELLED,
            ProcessInterface::EVENT_PROCESS_INSTANCE_COMPLETED,
        ]);
    }

    /**
     * Test MI task cancel instance
     *
     */
    public function testMultiInstanceSequentialLoopCardinalityCancelInstance()
    {
        // Load a BpmnFile Repository
        $bpmnRepository = new BpmnDocument();
        $bpmnRepository->setEngine($this->engine);
        $bpmnRepository->setFactory($this->repository);
        $bpmnRepository->load(__DIR__ . '/../Patterns/files/MultiInstance_AllBehavior.bpmn');

        // Load a process from a bpmn repository by Id
        $process = $bpmnRepository->getProcess('MultiInstance_AllBehavior');

        // Get 'start' activity of the process
        $activity = $bpmnRepository->getActivity('start');

        // Get 'multiInstanceTask' activity of the process
        $miTask = $bpmnRepository->getActivity('multiInstanceTask');

        // Start the process
        $instance = $process->call();
        $this->engine->runToNextState();

        // Assertion: A process has started.
        $this->assertEquals(1, $process->getInstances()->count());

        // Assertion: The process has started and the first activity was actived
        $this->assertEvents([
            ProcessInterface::EVENT_PROCESS_INSTANCE_CREATED,
            EventInterface::EVENT_EVENT_TRIGGERED,
            ActivityInterface::EVENT_ACTIVITY_ACTIVATED,
            ScriptTaskInterface::EVENT_SCRIPT_TASK_ACTIVATED,
        ]);

        // Complete the first activity.
        $token = $activity->getTokens($instance)->item(0);
        $activity->complete($token);
        $this->engine->runToNextState();

        // Assertion: The first activity was completed then 3 script task are activated
        $this->assertEvents([
            ActivityInterface::EVENT_ACTIVITY_COMPLETED,
            ActivityInterface::EVENT_ACTIVITY_CLOSED,

            ActivityInterface::EVENT_ACTIVITY_ACTIVATED,
            ScriptTaskInterface::EVENT_SCRIPT_TASK_ACTIVATED,
        ]);

        // Complete the first MI activity.
        $token = $miTask->getTokens($instance)->item(0);
        $activity->complete($token);
        $this->engine->runToNextState();

        // Assertion: The first MI task was completed 2 pending
        $this->assertEvents([
            ActivityInterface::EVENT_ACTIVITY_COMPLETED,
            ActivityInterface::EVENT_ACTIVITY_ACTIVATED,
            ScriptTaskInterface::EVENT_SCRIPT_TASK_ACTIVATED,
        ]);

        // Complete the second MI activity.
        $token = $miTask->getTokens($instance)->item(0);
        $activity->complete($token);
        $this->engine->runToNextState();

        // Assertion: The second MI task was completed 1 pending
        $this->assertEvents([
            ActivityInterface::EVENT_ACTIVITY_COMPLETED,
            ActivityInterface::EVENT_ACTIVITY_ACTIVATED,
            ScriptTaskInterface::EVENT_SCRIPT_TASK_ACTIVATED,
        ]);

        // Cancel the third MI activity.
        $token = $miTask->getTokens($instance)->item(0);
        $token->setStatus(ScriptTaskInterface::TOKEN_STATE_CLOSED);
        $this->engine->runToNextState();

        // Assertion: The thrid and last MI task was cancelled, then the process is completed
        $this->assertEvents([
            ActivityInterface::EVENT_ACTIVITY_CANCELLED,
            ProcessInterface::EVENT_PROCESS_INSTANCE_COMPLETED,
        ]);
    }
}
