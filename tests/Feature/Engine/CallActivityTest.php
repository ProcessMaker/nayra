<?php

namespace Tests\Feature\Engine;

use ProcessMaker\Nayra\Contracts\Bpmn\ActivityInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\EndEventInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\EventInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\ProcessInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\ScriptTaskInterface;
use ProcessMaker\Nayra\Storage\BpmnDocument;

/**
 * Test call activity element.
 *
 */
class CallActivityTest extends EngineTestCase
{

    /**
     * Test a call activity collaboration.
     *
     */
    public function testCallActivity()
    {
        //Load a BpmnFile Repository
        $bpmnRepository = new BpmnDocument();
        $bpmnRepository->setEngine($this->engine);
        $bpmnRepository->setFactory($this->factory);
        $bpmnRepository->load(__DIR__ . '/files/CallActivity_Process.bpmn');

        //Load a process from a bpmn repository by Id
        $bpmnRepository->getCollaboration('collaboration');
        $process = $bpmnRepository->getProcess('CallActivity_Process');
        $calledProcess = $bpmnRepository->getProcess('CalledProcess');

        $start = $bpmnRepository->getStartEvent('StartEvent_2');
        $task = $bpmnRepository->getActivity('ScriptTask_4');
        $subtask = $bpmnRepository->getScriptTask('ScriptTask_1');
        $endTask = $bpmnRepository->getScriptTask('ScriptTask_5');
        $callParticipant = $bpmnRepository->getParticipant('Participant_2');
        $calledParticipant = $bpmnRepository->getParticipant('Participant_1');

        //Assertion: Verify $callParticipant refers to $process
        $this->assertEquals($process, $callParticipant->getProcess());
        $this->assertEquals($calledProcess, $calledParticipant->getProcess());

        //Assertion: Verify default participant multiplicity ['maximum' => 1, 'minimum' => 0]
        $this->assertEquals(['maximum' => 1, 'minimum' => 0], $callParticipant->getParticipantMultiplicity());
        $this->assertEquals(['maximum' => 1, 'minimum' => 0], $calledParticipant->getParticipantMultiplicity());
        
        //Call a process
        $instance = $process->call();
        $this->engine->runToNextState();

        //Assertion: Expects that $process has one instance and $calledProcess does not have instances
        $this->assertEquals(1, $process->getInstances()->count());
        $this->assertEquals(0, $calledProcess->getInstances()->count());

        //Complete task
        $token = $task->getTokens($instance)->item(0);
        $task->complete($token);
        $this->engine->runToNextState();

        //Assertion: Expects that $calledProcess has one instance
        $this->assertEquals(1, $calledProcess->getInstances()->count());

        //Get instance of the process called by the CallActivity
        $subInstance = $calledProcess->getInstances()->item(0);

        //Assertion: Expects that $subtask owned by the $calledProcess has one token
        $this->assertEquals(1, $subtask->getTokens($subInstance)->count());

        //Assertion: $process is started, first activity completed and starts the subtask.
        $this->assertEvents([
            ProcessInterface::EVENT_PROCESS_INSTANCE_CREATED,
            EventInterface::EVENT_EVENT_TRIGGERED,
            ActivityInterface::EVENT_ACTIVITY_ACTIVATED,
            ScriptTaskInterface::EVENT_SCRIPT_TASK_ACTIVATED,
            ActivityInterface::EVENT_ACTIVITY_COMPLETED,
            ActivityInterface::EVENT_ACTIVITY_CLOSED,
            ActivityInterface::EVENT_ACTIVITY_ACTIVATED,
            ProcessInterface::EVENT_PROCESS_INSTANCE_CREATED,
            EventInterface::EVENT_EVENT_TRIGGERED,
            ActivityInterface::EVENT_ACTIVITY_ACTIVATED,
            ScriptTaskInterface::EVENT_SCRIPT_TASK_ACTIVATED,
        ]);

        //Complete the subtask
        $token = $subtask->getTokens($subInstance)->item(0);
        $subtask->complete($token);
        $this->engine->runToNextState();

        //Assertion: Subtask is completed, $calledProcess is completed, and next task is activated.
        $this->assertEvents([
            ActivityInterface::EVENT_ACTIVITY_COMPLETED,
            ActivityInterface::EVENT_ACTIVITY_CLOSED,
            EndEventInterface::EVENT_THROW_TOKEN_ARRIVES,
            EndEventInterface::EVENT_THROW_TOKEN_CONSUMED,
            EndEventInterface::EVENT_EVENT_TRIGGERED,
            ProcessInterface::EVENT_PROCESS_INSTANCE_COMPLETED,
            ActivityInterface::EVENT_ACTIVITY_COMPLETED,
            ActivityInterface::EVENT_ACTIVITY_CLOSED,
            ActivityInterface::EVENT_ACTIVITY_ACTIVATED,
            ScriptTaskInterface::EVENT_SCRIPT_TASK_ACTIVATED,
        ]);

        //Complete the last task
        $token = $endTask->getTokens($instance)->item(0);
        $endTask->complete($token);
        $this->engine->runToNextState();

        //Assertion: End Task is completed and $process is completed.
        $this->assertEvents([
            ActivityInterface::EVENT_ACTIVITY_COMPLETED,
            ActivityInterface::EVENT_ACTIVITY_CLOSED,
            EndEventInterface::EVENT_THROW_TOKEN_ARRIVES,
            EndEventInterface::EVENT_THROW_TOKEN_CONSUMED,
            EndEventInterface::EVENT_EVENT_TRIGGERED,
            ProcessInterface::EVENT_PROCESS_INSTANCE_COMPLETED,
        ]);
    }
}
