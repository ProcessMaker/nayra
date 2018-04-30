<?php

namespace Tests\Feature\Engine;

use ProcessMaker\Nayra\Contracts\Bpmn\ActivityInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\EventInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\ProcessInterface;
use ProcessMaker\Repositories\BpmnFileRepository;

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
        $bpmnRepository = new BpmnFileRepository();
        $bpmnRepository->setEngine($this->engine);
        $bpmnRepository->load(__DIR__ . '/files/CallActivity_Process.bpmn');

        //Load a process from a bpmn repository by Id
        $bpmnRepository->loadBpmElementById('collaboration');
        $process = $bpmnRepository->loadBpmElementById('CallActivity_Process');
        $calledProcess = $bpmnRepository->loadBpmElementById('CalledProcess');

        $start = $bpmnRepository->loadBpmElementById('StartEvent_2');
        $task = $bpmnRepository->loadBpmElementById('ScriptTask_4');
        $subtask = $bpmnRepository->loadBpmElementById('ScriptTask_1');
        $endTask = $bpmnRepository->loadBpmElementById('ScriptTask_5');
        $callParticipant = $bpmnRepository->loadBpmElementById('Participant_2');
        $calledParticipant = $bpmnRepository->loadBpmElementById('Participant_1');

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

        //Assertion: Expects that $subtask owned by the $calledProcess has one token
        $this->assertEquals(1, $subtask->getTokens($instance)->count());

        //Get instance of the process called by the CallActivity
        $instance = $calledProcess->getInstances()->item(0);

        //Assertion: $process is started, first activity completed and starts the subtask.
        $this->assertEvents([
            EventInterface::EVENT_EVENT_TRIGGERED,
            ActivityInterface::EVENT_ACTIVITY_ACTIVATED,
            ActivityInterface::EVENT_ACTIVITY_COMPLETED,
            ActivityInterface::EVENT_ACTIVITY_CLOSED,
            ActivityInterface::EVENT_ACTIVITY_ACTIVATED,
            EventInterface::EVENT_EVENT_TRIGGERED,
            ActivityInterface::EVENT_ACTIVITY_ACTIVATED,
        ]);

        //Complete the subtask
        $token = $subtask->getTokens($instance)->item(0);
        $subtask->complete($token);
        $this->engine->runToNextState();

        //Assertion: Subtask is completed, $calledProcess is completed, and next task is activated.
        $this->assertEvents([
            ActivityInterface::EVENT_ACTIVITY_COMPLETED,
            ActivityInterface::EVENT_ACTIVITY_CLOSED,
            EventInterface::EVENT_EVENT_TRIGGERED,
            ProcessInterface::EVENT_PROCESS_COMPLETED,
            ActivityInterface::EVENT_ACTIVITY_COMPLETED,
            ActivityInterface::EVENT_ACTIVITY_CLOSED,
            ActivityInterface::EVENT_ACTIVITY_ACTIVATED,
        ]);

        //Complete the subtask
        $token = $endTask->getTokens($instance)->item(0);
        $endTask->complete($token);
        $this->engine->runToNextState();

        //Assertion: End Task is completed and $process is completed.
        $this->assertEvents([
            ActivityInterface::EVENT_ACTIVITY_COMPLETED,
            ActivityInterface::EVENT_ACTIVITY_CLOSED,
            EventInterface::EVENT_EVENT_TRIGGERED,
            ProcessInterface::EVENT_PROCESS_COMPLETED,
        ]);
    }
}
