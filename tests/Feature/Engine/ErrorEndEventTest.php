<?php

namespace Tests\Feature\Engine;

use ProcessMaker\Nayra\Contracts\Bpmn\ActivityInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\EndEventInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\ErrorEventDefinitionInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\EventInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\ProcessInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\ScriptTaskInterface;
use ProcessMaker\Nayra\Storage\BpmnDocument;

/**
 * Test an error end event.
 *
 */
class ErrorEndEventTest extends EngineTestCase
{

    /**
     * Test a global Error End Event
     *
     */
    public function testErrorEndEventTopLevel()
    {
        //Load a BpmnFile Repository
        $bpmnRepository = new BpmnDocument();
        $bpmnRepository->setEngine($this->engine);
        $bpmnRepository->setFactory($this->repository);
        $bpmnRepository->load(__DIR__ . '/files/Error_EndEvent_TopLevel.bpmn');

        //Load a process from a bpmn repository by Id
        $process = $bpmnRepository->getProcess('Error_EndEvent_TopLevel');

        //Get start event and event definition references
        $startActivity = $bpmnRepository->getScriptTask('start');
        $endActivity = $bpmnRepository->getScriptTask('end');
        $errorEvent = $bpmnRepository->getErrorEventDefinition('TerminateEventDefinition_1');
        $error = $bpmnRepository->getError('error');

        //Start the process
        $instance = $process->call();
        $this->engine->runToNextState();

        //Complete the 'start' activity
        $token = $startActivity->getTokens($instance)->item(0);
        $startActivity->complete($token);
        $this->engine->runToNextState();

        //Complete the 'end' activity
        $token = $endActivity->getTokens($instance)->item(0);
        $endActivity->complete($token);
        $this->engine->runToNextState();

        //Assertion: Verify that Error End Event was triggered and the process is completed
        $this->assertEvents([
            ProcessInterface::EVENT_PROCESS_INSTANCE_CREATED,
            EventInterface::EVENT_EVENT_TRIGGERED,
            ActivityInterface::EVENT_ACTIVITY_ACTIVATED,
            ScriptTaskInterface::EVENT_SCRIPT_TASK_ACTIVATED,
            ActivityInterface::EVENT_ACTIVITY_COMPLETED,
            ActivityInterface::EVENT_ACTIVITY_CLOSED,
            ActivityInterface::EVENT_ACTIVITY_ACTIVATED,
            ScriptTaskInterface::EVENT_SCRIPT_TASK_ACTIVATED,
            ActivityInterface::EVENT_ACTIVITY_COMPLETED,
            ActivityInterface::EVENT_ACTIVITY_CLOSED,

            EndEventInterface::EVENT_THROW_TOKEN_ARRIVES,
            ErrorEventDefinitionInterface::EVENT_THROW_EVENT_DEFINITION,
            EndEventInterface::EVENT_THROW_TOKEN_CONSUMED,
            EventInterface::EVENT_EVENT_TRIGGERED,
            ProcessInterface::EVENT_PROCESS_INSTANCE_COMPLETED,
        ]);
    }

    /**
     * Test a global Error End Event
     *
     */
    public function testErrorEndEventCallActivity()
    {
        //Load a BpmnFile Repository
        $bpmnRepository = new BpmnDocument();
        $bpmnRepository->setEngine($this->engine);
        $bpmnRepository->setFactory($this->repository);
        $bpmnRepository->load(__DIR__ . '/files/Error_EndEvent_CallActivity.bpmn');

        //Load a process from a bpmn repository by Id
        $process = $bpmnRepository->getProcess('PROCESS_1');
        $subProcess = $bpmnRepository->getProcess('PROCESS_2');

        //Get start event and event definition references
        $callActivity = $bpmnRepository->getCallActivity('_5');
        $subActivity = $bpmnRepository->getActivity('_10');

        //Start
        $instance = $process->call();
        $this->engine->runToNextState();

        $subInstance = $subProcess->getInstances()->item(0);

        //Completes 'start' and 'end' activities
        $token = $subActivity->getTokens($subInstance)->item(0);
        $subActivity->complete($token);
        $this->engine->runToNextState();

        //Assertion: Verify that ErrorEventDefinition was triggered
        //Assertion: Verify that the Activity goes to exception state
        //Assertion: Verify that the subprocess is ended
        $this->assertEvents([
            ProcessInterface::EVENT_PROCESS_INSTANCE_CREATED,
            EventInterface::EVENT_EVENT_TRIGGERED,
            ActivityInterface::EVENT_ACTIVITY_ACTIVATED,
            ProcessInterface::EVENT_PROCESS_INSTANCE_CREATED,
            EventInterface::EVENT_EVENT_TRIGGERED,
            ActivityInterface::EVENT_ACTIVITY_ACTIVATED,
            ActivityInterface::EVENT_ACTIVITY_COMPLETED,
            ActivityInterface::EVENT_ACTIVITY_CLOSED,
            EndEventInterface::EVENT_THROW_TOKEN_ARRIVES,
            ErrorEventDefinitionInterface::EVENT_THROW_EVENT_DEFINITION,
            EndEventInterface::EVENT_THROW_TOKEN_CONSUMED,
            EndEventInterface::EVENT_EVENT_TRIGGERED,
            ProcessInterface::EVENT_PROCESS_INSTANCE_COMPLETED,
            ActivityInterface::EVENT_ACTIVITY_EXCEPTION,
        ]);
    }
}
