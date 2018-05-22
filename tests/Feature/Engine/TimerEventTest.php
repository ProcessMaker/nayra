<?php

namespace Tests\Feature\Engine;

use ProcessMaker\Repositories\BpmnFileRepository;

/**
 * Test a terminate event.
 *
 */
class TimerEventTest extends EngineTestCase
{

    /**
     * Test terminate end event
     *
     */
    public function testTerminateEndEvent()
    {
        //Load a BpmnFile Repository
        $bpmnRepository = new BpmnFileRepository();
        $bpmnRepository->setEngine($this->engine);
        $bpmnRepository->load(__DIR__ . '/files/Timer_StartEvent_TimeCycle.bpmn');

        //Load a process from a bpmn repository by Id
        $process = $bpmnRepository->loadBpmElementById('Process');
        $startEvent = $bpmnRepository->loadBpmElementById('_9');

        //Assertion: The jobs manager receive a scheduling request to trigger the start event time cycle specified in the process

        //Foce to dispatch the requersted job
        $this->dispatchJob();

        //Foce to dispatch the requersted job
        $this->dispatchJob();
    }
}
