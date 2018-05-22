<?php

namespace Tests\Feature\Engine;

use ProcessMaker\Repositories\BpmnFileRepository;

/**
 * Timer Event tests
 *
 */
class TimerEventTest extends EngineTestCase
{

    /**
     * Test the start timer event with a date time specified in ISO8601 format.
     *
     */
    public function testStartTimerEventWithTimeDate()
    {
        //Load a BpmnFile Repository
        $bpmnRepository = new BpmnFileRepository();
        $bpmnRepository->setEngine($this->engine);
        $bpmnRepository->load(__DIR__ . '/files/Timer_StartEvent_TimeDate.bpmn');

        //Load a process from a bpmn repository by Id
        $process = $bpmnRepository->loadBpmElementById('Process');
        $startEvent = $bpmnRepository->loadBpmElementById('_9');

        //Assertion: The jobs manager receive a scheduling request to trigger the start event time cycle specified in the process
        $this->assertScheduledCyclicTimer('R/PT1M', $startEvent);

        //Force to dispatch the requersted job
        $this->dispatchJob();
        $this->assertEquals(1, $process->getInstances()->count());
    }

    /**
     * Test the start timer event with a time cycle specified in ISO8601 format.
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
        $this->assertScheduledCyclicTimer('R/PT1M', $startEvent);

        //Force to dispatch the requersted job
        $this->dispatchJob();
        $this->assertEquals(1, $process->getInstances()->count());

        //Force to dispatch the requersted job
        $this->dispatchJob();
        $this->assertEquals(2, $process->getInstances()->count());
    }
}
