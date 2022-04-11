<?php

namespace Tests\Feature\Engine;

use ProcessMaker\Nayra\Contracts\Bpmn\ActivityInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\EventInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\GatewayInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\ProcessInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\ScriptTaskInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\TerminateEventDefinitionInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\ThrowEventInterface;
use ProcessMaker\Nayra\Storage\BpmnDocument;
use ProcessMaker\Repositories\BpmnFileRepository;

/**
 * Test a terminate event.
 *
 */
class TerminateEventTest extends EngineTestCase
{

    /**
     * Test terminate end event
     *
     */
    public function testTerminateEndEvent()
    {
        //Load a BpmnFile Repository
        $bpmnRepository = new BpmnDocument();
        $bpmnRepository->setEngine($this->engine);
        $bpmnRepository->setFactory($this->repository);
        $bpmnRepository->load(__DIR__ . '/files/Terminate_Event.bpmn');

        //Load a process from a bpmn repository by Id
        $process = $bpmnRepository->getProcess('Terminate_Event');

        //Get 'start' activity of the process
        $activity = $bpmnRepository->getActivity('start');

        //Get the terminate event
        $terminateEvent = $bpmnRepository->getEndEvent('EndEvent_1');

        //Start the process
        $instance = $process->call();
        $this->engine->runToNextState();

        //Assertion: A process has started.
        $this->assertEquals(1, $process->getInstances()->count());

        //Assertion: The process has started and the first activity was actived
        $this->assertEvents([
            ProcessInterface::EVENT_PROCESS_INSTANCE_CREATED,
            EventInterface::EVENT_EVENT_TRIGGERED,
            ActivityInterface::EVENT_ACTIVITY_ACTIVATED,
            ScriptTaskInterface::EVENT_SCRIPT_TASK_ACTIVATED,
        ]);

        //Complete the first activity.
        $token = $activity->getTokens($instance)->item(0);
        $activity->complete($token);
        $this->engine->runToNextState();

        //Assertion: The activity was completed
        //Assertion: The gateway was activated
        //Assertion: The second activity was activated
        //Assertion: The terminate event was activated and the process was ended
        $this->assertEvents([
            ActivityInterface::EVENT_ACTIVITY_COMPLETED,
            ActivityInterface::EVENT_ACTIVITY_CLOSED,
            GatewayInterface::EVENT_GATEWAY_TOKEN_ARRIVES,
            GatewayInterface::EVENT_GATEWAY_ACTIVATED,
            GatewayInterface::EVENT_GATEWAY_TOKEN_CONSUMED,
            GatewayInterface::EVENT_GATEWAY_TOKEN_PASSED,
            GatewayInterface::EVENT_GATEWAY_TOKEN_PASSED,
            ThrowEventInterface::EVENT_THROW_TOKEN_ARRIVES,
            TerminateEventDefinitionInterface::EVENT_THROW_EVENT_DEFINITION,
            ActivityInterface::EVENT_ACTIVITY_ACTIVATED,
            ScriptTaskInterface::EVENT_SCRIPT_TASK_ACTIVATED,
            ThrowEventInterface::EVENT_THROW_TOKEN_CONSUMED,
            EventInterface::EVENT_EVENT_TRIGGERED,
            ActivityInterface::EVENT_ACTIVITY_CANCELLED,
            ProcessInterface::EVENT_PROCESS_INSTANCE_COMPLETED,
        ]);
    }
}
