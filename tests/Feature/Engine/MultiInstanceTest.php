<?php

namespace Tests\Feature\Engine;

use ProcessMaker\Nayra\Contracts\Bpmn\ActivityInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\EndEventInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\EventInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\ProcessInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\ScriptTaskInterface;
use ProcessMaker\Nayra\Contracts\Engine\ExecutionInstanceInterface;
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

        // Assertion: The internal data of the LoopCharacteristics are stored in the instance data.
        $this->verifyStoredInstanceData($instance);

        // Complete the first MI activity.
        $token = $miTask->getTokens($instance)->item(0);
        $activity->complete($token);
        $this->engine->runToNextState();

        // Assertion: The first MI task was completed 2 pending
        $this->assertEvents([
            ActivityInterface::EVENT_ACTIVITY_COMPLETED,
        ]);

        // Assertion: The internal data of the LoopCharacteristics are stored in the instance data.
        $this->verifyStoredInstanceData($instance);

        // Complete the second MI activity.
        $token = $miTask->getTokens($instance)->item(0);
        $activity->complete($token);
        $this->engine->runToNextState();

        // Assertion: The second MI task was completed 1 pending
        $this->assertEvents([
            ActivityInterface::EVENT_ACTIVITY_COMPLETED,
        ]);

        // Assertion: The internal data of the LoopCharacteristics are stored in the instance data.
        $this->verifyStoredInstanceData($instance);

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

        // Assertion: The internal data of the LoopCharacteristics are stored in the instance data.
        $this->verifyStoredInstanceData($instance);
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

        // Assertion: The internal data of the LoopCharacteristics are stored in the instance data.
        $this->verifyStoredInstanceData($instance);

        // Complete the first MI activity.
        $token = $miTask->getTokens($instance)->item(0);
        $activity->complete($token);
        $this->engine->runToNextState();

        // Assertion: The first MI task was completed 2 pending
        $this->assertEvents([
            ActivityInterface::EVENT_ACTIVITY_COMPLETED,
        ]);

        // Assertion: The internal data of the LoopCharacteristics are stored in the instance data.
        $this->verifyStoredInstanceData($instance);

        // Complete the second MI activity.
        $token = $miTask->getTokens($instance)->item(0);
        $activity->complete($token);
        $this->engine->runToNextState();

        // Assertion: The second MI task was completed 1 pending
        $this->assertEvents([
            ActivityInterface::EVENT_ACTIVITY_COMPLETED,
        ]);

        // Assertion: The internal data of the LoopCharacteristics are stored in the instance data.
        $this->verifyStoredInstanceData($instance);

        // Complete the third MI activity.
        $token = $miTask->getTokens($instance)->item(0);
        $token->setStatus(ScriptTaskInterface::TOKEN_STATE_FAILING);
        $this->engine->runToNextState();

        // Assertion: Fail thrid MI task
        $this->assertEvents([
            ActivityInterface::EVENT_ACTIVITY_EXCEPTION,
        ]);

        // Assertion: The internal data of the LoopCharacteristics are stored in the instance data.
        $this->verifyStoredInstanceData($instance);

        // Close failing task instance.
        $token->setStatus(ScriptTaskInterface::TOKEN_STATE_CLOSED);
        $this->engine->runToNextState();

        // Assertion: The thrid MI task was cancelled, the MI Activity hangs because it could not close all the parallel instances
        $this->assertEvents([
            ActivityInterface::EVENT_ACTIVITY_CANCELLED,
        ]);

        // Assertion: The internal data of the LoopCharacteristics are stored in the instance data.
        $this->verifyStoredInstanceData($instance);
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

        // Assertion: The internal data of the LoopCharacteristics are stored in the instance data.
        $this->verifyStoredInstanceData($instance);

        // Complete the first MI activity.
        $token = $miTask->getTokens($instance)->item(0);
        $activity->complete($token);
        $this->engine->runToNextState();

        // Assertion: The first MI task was completed 2 pending
        $this->assertEvents([
            ActivityInterface::EVENT_ACTIVITY_COMPLETED,
        ]);

        // Assertion: The internal data of the LoopCharacteristics are stored in the instance data.
        $this->verifyStoredInstanceData($instance);

        // Complete the second MI activity.
        $token = $miTask->getTokens($instance)->item(0);
        $activity->complete($token);
        $this->engine->runToNextState();

        // Assertion: The second MI task was completed 1 pending
        $this->assertEvents([
            ActivityInterface::EVENT_ACTIVITY_COMPLETED,
        ]);

        // Assertion: The internal data of the LoopCharacteristics are stored in the instance data.
        $this->verifyStoredInstanceData($instance);

        // Cancel the third MI activity.
        $token = $miTask->getTokens($instance)->item(0);
        $token->setStatus(ScriptTaskInterface::TOKEN_STATE_CLOSED);
        $this->engine->runToNextState();

        // Assertion: The thrid MI task was cancelled, the MI Activity hangs because it could not close all the parallel instances
        $this->assertEvents([
            ActivityInterface::EVENT_ACTIVITY_CANCELLED,
        ]);

        // Assertion: The internal data of the LoopCharacteristics are stored in the instance data.
        $this->verifyStoredInstanceData($instance);
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

        // Assertion: The internal data of the LoopCharacteristics are stored in the instance data.
        $this->verifyStoredInstanceData($instance);

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

        // Assertion: The internal data of the LoopCharacteristics are stored in the instance data.
        $this->verifyStoredInstanceData($instance);

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

        // Assertion: The internal data of the LoopCharacteristics are stored in the instance data.
        $this->verifyStoredInstanceData($instance);

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

        // Assertion: The internal data of the LoopCharacteristics are stored in the instance data.
        $this->verifyStoredInstanceData($instance);
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

        // Assertion: The internal data of the LoopCharacteristics are stored in the instance data.
        $this->verifyStoredInstanceData($instance);

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

        // Assertion: The internal data of the LoopCharacteristics are stored in the instance data.
        $this->verifyStoredInstanceData($instance);

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

        // Assertion: The internal data of the LoopCharacteristics are stored in the instance data.
        $this->verifyStoredInstanceData($instance);

        // Complete the third MI activity.
        $token = $miTask->getTokens($instance)->item(0);
        $token->setStatus(ScriptTaskInterface::TOKEN_STATE_FAILING);
        $this->engine->runToNextState();

        // Assertion: Fail thrid MI task, the MI Activity hangs until the failing instance is closed
        $this->assertEvents([
            ActivityInterface::EVENT_ACTIVITY_EXCEPTION,
        ]);

        // Assertion: The internal data of the LoopCharacteristics are stored in the instance data.
        $this->verifyStoredInstanceData($instance);

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

        // Assertion: The internal data of the LoopCharacteristics are stored in the instance data.
        $this->verifyStoredInstanceData($instance);

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

        // Assertion: The internal data of the LoopCharacteristics are stored in the instance data.
        $this->verifyStoredInstanceData($instance);

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

        // Assertion: The internal data of the LoopCharacteristics are stored in the instance data.
        $this->verifyStoredInstanceData($instance);

        // Cancel the third MI activity.
        $token = $miTask->getTokens($instance)->item(0);
        $token->setStatus(ScriptTaskInterface::TOKEN_STATE_CLOSED);
        $this->engine->runToNextState();

        // Assertion: The internal data of the LoopCharacteristics are stored in the instance data.
        $this->verifyStoredInstanceData($instance);

        // Assertion: The thrid and last MI task was cancelled, then the process is completed
        $this->assertEvents([
            ActivityInterface::EVENT_ACTIVITY_CANCELLED,
            ProcessInterface::EVENT_PROCESS_INSTANCE_COMPLETED,
        ]);
    }

    /**
     * Tests a process with MI as marker but not executable
     *
     * @return void
     */
    public function testUnderspecifiedLoop()
    {
        // Load a BpmnFile Repository
        $bpmnRepository = new BpmnDocument();
        $bpmnRepository->setEngine($this->engine);
        $bpmnRepository->setFactory($this->repository);
        $bpmnRepository->load(__DIR__ . '/files/MultiInstance_DocumentOnly.bpmn');

        // Load a process from a bpmn repository by Id
        $process = $bpmnRepository->getProcess('MultiInstance_DocumentOnly');

        // Get 'start' activity of the process
        $activity = $bpmnRepository->getActivity('start');

        // Get 'MultiInstanceTask' activity of the process
        $miTask = $bpmnRepository->getActivity('MultiInstanceTask');

        // Get 'last task' activity
        $lastTask = $bpmnRepository->getActivity('node_8');

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
        ]);

        // Complete the first activity.
        $token = $activity->getTokens($instance)->item(0);
        $activity->complete($token);
        $this->engine->runToNextState();

        // Assertion: The first activity was completed then only 1 task are activated (as a normal task)
        $this->assertEvents([
            ActivityInterface::EVENT_ACTIVITY_COMPLETED,
            ActivityInterface::EVENT_ACTIVITY_CLOSED,

            ActivityInterface::EVENT_ACTIVITY_ACTIVATED,
        ]);

        // Assertion: The internal data of the LoopCharacteristics are stored in the instance data.
        $this->verifyStoredInstanceData($instance);

        // Complete the MI activity.
        $token = $miTask->getTokens($instance)->item(0);
        $activity->complete($token);
        $this->engine->runToNextState();

        // Assertion: The MI task was completed and next task is activated
        $this->assertEvents([
            ActivityInterface::EVENT_ACTIVITY_COMPLETED,
            ActivityInterface::EVENT_ACTIVITY_CLOSED,

            ActivityInterface::EVENT_ACTIVITY_ACTIVATED,
        ]);

        // Assertion: The internal data of the LoopCharacteristics are stored in the instance data.
        $this->verifyStoredInstanceData($instance);

        // Complete the last task
        $token = $lastTask->getTokens($instance)->item(0);
        $activity->complete($token);
        $this->engine->runToNextState();

        // Assertion: The last task was completed and process is completed
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
     * Tests a multi-instance with input-output data
     *
     * @return void
     */
    public function testMultiInstanceInputOutput()
    {
        // Load a BpmnFile Repository
        $bpmnRepository = new BpmnDocument();
        $bpmnRepository->setEngine($this->engine);
        $bpmnRepository->setFactory($this->repository);
        $bpmnRepository->load(__DIR__ . '/files/MultiInstance_InputOutput.bpmn');

        // Load a process from a bpmn repository by Id
        $process = $bpmnRepository->getProcess('MultiInstance_Process');

        // Get 'start' activity of the process
        $activity = $bpmnRepository->getActivity('start');

        // Get 'MultiInstanceTask' activity of the process
        $miTask = $bpmnRepository->getScriptTask('MultiInstanceTask');

        // Get 'last task' activity
        $lastTask = $bpmnRepository->getActivity('end');

        // Start the process
        $instance = $process->call();
        $instance->getDataStore()->putData('users', [
            ['name' => 'Marco', 'age' => 20],
            ['name' => 'Jonas', 'age' => 23],
        ]);
        $this->engine->runToNextState();

        // Assertion: A process has started.
        $this->assertEquals(1, $process->getInstances()->count());

        // Assertion: The process has started and the first activity was actived
        $this->assertEvents([
            ProcessInterface::EVENT_PROCESS_INSTANCE_CREATED,
            EventInterface::EVENT_EVENT_TRIGGERED,
            ActivityInterface::EVENT_ACTIVITY_ACTIVATED,
        ]);

        // Complete the first activity.
        $token = $activity->getTokens($instance)->item(0);
        $activity->complete($token);
        $this->engine->runToNextState();

        // Assertion: The first activity was completed then 2 script task are started
        $this->assertEvents([
            ActivityInterface::EVENT_ACTIVITY_COMPLETED,
            ActivityInterface::EVENT_ACTIVITY_CLOSED,

            ActivityInterface::EVENT_ACTIVITY_ACTIVATED,
            ScriptTaskInterface::EVENT_SCRIPT_TASK_ACTIVATED,
            ActivityInterface::EVENT_ACTIVITY_ACTIVATED,
            ScriptTaskInterface::EVENT_SCRIPT_TASK_ACTIVATED,
        ]);

        // Assertion: The internal data of the LoopCharacteristics are stored in the instance data.
        $this->verifyStoredInstanceData($instance);

        // Complete the MI activity.
        $token1 = $miTask->getTokens($instance)->item(0);
        $token2 = $miTask->getTokens($instance)->item(1);
        $miTask->runScript($token1);
        $miTask->runScript($token2);
        $this->engine->runToNextState();

        // Assertion: The MI task was completed and next task is activated
        $this->assertEvents([
            ActivityInterface::EVENT_ACTIVITY_COMPLETED,
            ActivityInterface::EVENT_ACTIVITY_COMPLETED,
            ActivityInterface::EVENT_ACTIVITY_CLOSED,
            ActivityInterface::EVENT_ACTIVITY_CLOSED,

            ActivityInterface::EVENT_ACTIVITY_ACTIVATED,
        ]);

        // Assertion: The internal data of the LoopCharacteristics are stored in the instance data.
        $this->verifyStoredInstanceData($instance);

        // Complete the last task
        $token = $lastTask->getTokens($instance)->item(0);
        $activity->complete($token);
        $this->engine->runToNextState();

        // Assertion: The last task was completed and process is completed
        $this->assertEvents([
            ActivityInterface::EVENT_ACTIVITY_COMPLETED,
            ActivityInterface::EVENT_ACTIVITY_CLOSED,
            EndEventInterface::EVENT_THROW_TOKEN_ARRIVES,
            EndEventInterface::EVENT_THROW_TOKEN_CONSUMED,
            EndEventInterface::EVENT_EVENT_TRIGGERED,

            ProcessInterface::EVENT_PROCESS_INSTANCE_COMPLETED,
        ]);

        // Assertion: The internal data of the LoopCharacteristics are stored in the instance data.
        $this->verifyStoredInstanceData($instance);

        // Assertion: Output data 'result' should contain the expected result
        $data = $instance->getDataStore()->getData();
        $this->assertArrayHasKey('result', $data);
        $this->assertEquals([
            [
                'name' => 'Marco',
                'age' => 21,
            ],
            [
                'name' => 'Jonas',
                'age' => 24,
            ],
        ], $data['result']);
    }

    /**
     * Tests a multi-instance with input-output data but without inputDataItem
     * nor outputDataItem
     *
     * @return void
     */
    public function testMultiInstanceInputOutputWithoutInputItemOututItem()
    {
        // Load a BpmnFile Repository
        $bpmnRepository = new BpmnDocument();
        $bpmnRepository->setEngine($this->engine);
        $bpmnRepository->setFactory($this->repository);
        $bpmnRepository->load(__DIR__ . '/files/MultiInstance_InputOutput_WithoutIODataItem.bpmn');

        // Load a process from a bpmn repository by Id
        $process = $bpmnRepository->getProcess('MultiInstance_Process');

        // Get 'start' activity of the process
        $activity = $bpmnRepository->getActivity('start');

        // Get 'MultiInstanceTask' activity of the process
        $miTask = $bpmnRepository->getScriptTask('MultiInstanceTask');

        // Get 'last task' activity
        $lastTask = $bpmnRepository->getActivity('end');

        // Start the process
        $instance = $process->call();
        $instance->getDataStore()->putData('users', [
            ['name' => 'Marco', 'age' => 20],
            ['name' => 'Jonas', 'age' => 23],
        ]);
        $this->engine->runToNextState();

        // Assertion: A process has started.
        $this->assertEquals(1, $process->getInstances()->count());

        // Assertion: The process has started and the first activity was actived
        $this->assertEvents([
            ProcessInterface::EVENT_PROCESS_INSTANCE_CREATED,
            EventInterface::EVENT_EVENT_TRIGGERED,
            ActivityInterface::EVENT_ACTIVITY_ACTIVATED,
        ]);

        // Complete the first activity.
        $token = $activity->getTokens($instance)->item(0);
        $activity->complete($token);
        $this->engine->runToNextState();

        // Assertion: The first activity was completed then 2 script task are started
        $this->assertEvents([
            ActivityInterface::EVENT_ACTIVITY_COMPLETED,
            ActivityInterface::EVENT_ACTIVITY_CLOSED,

            ActivityInterface::EVENT_ACTIVITY_ACTIVATED,
            ScriptTaskInterface::EVENT_SCRIPT_TASK_ACTIVATED,
            ActivityInterface::EVENT_ACTIVITY_ACTIVATED,
            ScriptTaskInterface::EVENT_SCRIPT_TASK_ACTIVATED,
        ]);

        // Assertion: The internal data of the LoopCharacteristics are stored in the instance data.
        $this->verifyStoredInstanceData($instance);

        // Complete the MI activity.
        $token1 = $miTask->getTokens($instance)->item(0);
        $token2 = $miTask->getTokens($instance)->item(1);
        $miTask->runScript($token1);
        $miTask->runScript($token2);
        $this->engine->runToNextState();

        // Assertion: The MI task was completed and next task is activated
        $this->assertEvents([
            ActivityInterface::EVENT_ACTIVITY_COMPLETED,
            ActivityInterface::EVENT_ACTIVITY_COMPLETED,
            ActivityInterface::EVENT_ACTIVITY_CLOSED,
            ActivityInterface::EVENT_ACTIVITY_CLOSED,

            ActivityInterface::EVENT_ACTIVITY_ACTIVATED,
        ]);

        // Assertion: The internal data of the LoopCharacteristics are stored in the instance data.
        $this->verifyStoredInstanceData($instance);

        // Complete the last task
        $token = $lastTask->getTokens($instance)->item(0);
        $activity->complete($token);
        $this->engine->runToNextState();

        // Assertion: The last task was completed and process is completed
        $this->assertEvents([
            ActivityInterface::EVENT_ACTIVITY_COMPLETED,
            ActivityInterface::EVENT_ACTIVITY_CLOSED,
            EndEventInterface::EVENT_THROW_TOKEN_ARRIVES,
            EndEventInterface::EVENT_THROW_TOKEN_CONSUMED,
            EndEventInterface::EVENT_EVENT_TRIGGERED,

            ProcessInterface::EVENT_PROCESS_INSTANCE_COMPLETED,
        ]);

        // Assertion: Output data 'result' should contain the expected result
        $data = $instance->getDataStore()->getData();
        $this->assertArrayHasKey('result', $data);
        $this->assertEquals([
            [
                'name' => 'Marco',
                'age' => 21,
                'loopCounter' => 1,
            ],
            [
                'name' => 'Jonas',
                'age' => 24,
                'loopCounter' => 2,
            ],
        ], $data['result']);
    }

    /**
     * Verify that the data of the instance coincide with the data stored
     *
     * @param ExecutionInstanceInterface $instance
     *
     * @return void
     */
    private function verifyStoredInstanceData(ExecutionInstanceInterface $instance)
    {
        $instanceId=$instance->getId();
        $instanceData = $instance->getDataStore()->getData();

        $instanceRepository = $this->repository->createExecutionInstanceRepository();
        $storageData = $instanceRepository->getInstanceData($instanceId);

        $this->assertEquals($instanceData, $storageData);
    }

    /**
     * Tests a process with MI with empty InputItems
     *
     * @return void
     */
    public function testLoopWithEmptyInputItems()
    {
        // Load a BpmnFile Repository
        $bpmnRepository = new BpmnDocument();
        $bpmnRepository->setEngine($this->engine);
        $bpmnRepository->setFactory($this->repository);
        $bpmnRepository->load(__DIR__ . '/../Patterns/files/MultiInstance_EmptyInputItems.bpmn');

        // Load a process from a bpmn repository by Id
        $process = $bpmnRepository->getProcess('ProcessId');

        // Get 'task_1' activity of the process
        $taskOne = $bpmnRepository->getActivity('task_1');

        // Get 'MultiInstanceTask' activity of the process
        $miTask = $bpmnRepository->getActivity('task_2');

        // Get 'last task' activity
        $lastTask = $bpmnRepository->getActivity('task_3');

        // Start the process with empty input items
        $instance = $process->call();
        $instance->getDataStore()->putData('items', []);
        $this->engine->runToNextState();

        // Assertion: A process has started.
        $this->assertEquals(1, $process->getInstances()->count());

        // Assertion: The process has started and the first activity was actived
        $this->assertEvents([
            ProcessInterface::EVENT_PROCESS_INSTANCE_CREATED,
            EventInterface::EVENT_EVENT_TRIGGERED,
            ActivityInterface::EVENT_ACTIVITY_ACTIVATED,
        ]);

        // Complete the first activity.
        $token = $taskOne->getTokens($instance)->item(0);
        $taskOne->complete($token);
        $this->engine->runToNextState();

        // Assertion: The first activity was completed, the task of multiple instances skipped and continued to the last task.
        $this->assertEvents([
            ActivityInterface::EVENT_ACTIVITY_COMPLETED,
            ActivityInterface::EVENT_ACTIVITY_CLOSED,
            ActivityInterface::EVENT_ACTIVITY_EXCEPTION,
        ]);

        // Assertion: The internal data of the LoopCharacteristics are stored in the instance data.
        $this->verifyStoredInstanceData($instance);
    }

    /**
     * Tests a process with MI with empty InputItems
     *
     * @return void
     */
    public function testLoopWithCardinalityZero()
    {
        // Load a BpmnFile Repository
        $bpmnRepository = new BpmnDocument();
        $bpmnRepository->setEngine($this->engine);
        $bpmnRepository->setFactory($this->repository);
        $bpmnRepository->load(__DIR__ . '/../Patterns/files/MultiInstance_CardinalityZero.bpmn');

        // Load a process from a bpmn repository by Id
        $process = $bpmnRepository->getProcess('ProcessId');

        // Get 'task_1' activity of the process
        $taskOne = $bpmnRepository->getActivity('task_1');

        // Get 'MultiInstanceTask' activity of the process
        $miTask = $bpmnRepository->getActivity('task_2');

        // Get 'last task' activity
        $lastTask = $bpmnRepository->getActivity('task_3');

        // Start the process with empty input items
        $instance = $process->call();
        $instance->getDataStore()->putData('times', 0);
        $this->engine->runToNextState();

        // Assertion: A process has started.
        $this->assertEquals(1, $process->getInstances()->count());

        // Assertion: The process has started and the first activity was actived
        $this->assertEvents([
            ProcessInterface::EVENT_PROCESS_INSTANCE_CREATED,
            EventInterface::EVENT_EVENT_TRIGGERED,
            ActivityInterface::EVENT_ACTIVITY_ACTIVATED,
        ]);

        // Complete the first activity.
        $token = $taskOne->getTokens($instance)->item(0);
        $taskOne->complete($token);
        $this->engine->runToNextState();

        // Assertion: The first activity was completed, the task of multiple instances skipped and continued to the last task.
        $this->assertEvents([
            ActivityInterface::EVENT_ACTIVITY_COMPLETED,
            ActivityInterface::EVENT_ACTIVITY_CLOSED,
            ActivityInterface::EVENT_ACTIVITY_EXCEPTION,
        ]);

        // Assertion: The internal data of the LoopCharacteristics are stored in the instance data.
        $this->verifyStoredInstanceData($instance);
    }
}
