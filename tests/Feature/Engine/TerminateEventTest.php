<?php

namespace Tests\Feature\Engine;

use ProcessMaker\Nayra\Contracts\Bpmn\ActivityInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\EventInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\GatewayInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\ProcessInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\TerminateEventDefinitionInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\ThrowEventInterface;
use ProcessMaker\Repositories\BpmnFileRepository;

/**
 * Test a terminate event.
 *
 */
class TerminateEventTest extends EngineTestCase
{

    /**
     * Test conditional start event
     *
     */
    public function testConditionalStartEvent()
    {
        //Load a BpmnFile Repository
        $bpmnRepository = new BpmnFileRepository();
        $bpmnRepository->setEngine($this->engine);
        $bpmnRepository->load(__DIR__ . '/files/Terminate_Event.bpmn');

        //Load a process from a bpmn repository by Id
        $process = $bpmnRepository->loadBpmElementById('Terminate_Event');

        //Get 'start' activity of the process
        $activity = $bpmnRepository->loadBpmElementById('start');

        //Get the terminate event
        $terminateEvent = $bpmnRepository->loadBpmElementById('EndEvent_1');

        //Start the process
        $instance = $process->call();
        $this->engine->runToNextState();

        //Assertion: A process has started.
        $this->assertEquals(1, $process->getInstances()->count());

        //Assertion: The process has started and the first activity was actived
        $this->assertEvents([
            EventInterface::EVENT_EVENT_TRIGGERED,
            ActivityInterface::EVENT_ACTIVITY_ACTIVATED,
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
            ActivityInterface::EVENT_ACTIVITY_ACTIVATED,
            GatewayInterface::EVENT_GATEWAY_TOKEN_PASSED,
            ThrowEventInterface::EVENT_THROW_TOKEN_ARRIVES,
            TerminateEventDefinitionInterface::EVENT_THROW_EVENT_DEFINITION,
            ThrowEventInterface::EVENT_THROW_TOKEN_CONSUMED,
            EventInterface::EVENT_EVENT_TRIGGERED,
            ProcessInterface::EVENT_PROCESS_COMPLETED,
        ]);
    }
}
