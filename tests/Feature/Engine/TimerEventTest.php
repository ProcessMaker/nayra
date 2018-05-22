<?php

namespace Tests\Feature\Engine;

use DateTime;
use DateInterval;
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
        $this->assertScheduledCyclicTimer('2018-05-01T14:30:33', $startEvent);

        //Force to dispatch the requersted job
        $this->dispatchJob();
        $this->assertEquals(1, $process->getInstances()->count());
    }

    /**
     * Test the start timer event with a time cycle specified in ISO8601 format.
     *
     */
    public function testStartTimerEventWithTimeCycle()
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

    /**
     * Test the start timer event with a date time specified by an expression.
     *
     */
    public function testStartTimerEventWithTimeDateExpression()
    {
        //Load a BpmnFile Repository
        $bpmnRepository = new BpmnFileRepository();
        $bpmnRepository->setEngine($this->engine);
        $bpmnRepository->load(__DIR__ . '/files/Timer_StartEvent_TimeDateExpression.bpmn');

        //Load a process from a bpmn repository by Id
        $process = $bpmnRepository->loadBpmElementById('Process');
        $startEvent = $bpmnRepository->loadBpmElementById('_9');

        $this->engine->loadProcess($process);

        //Assertion: The jobs manager receive a scheduling request to trigger the start event at the date time calculated by the expression
        $date = new DateTime;
        $date->setTime(23, 59, 59);
        $calculatedDate = $date->format(DateTime::ISO8601);
        $this->assertScheduledCyclicTimer($calculatedDate, $startEvent);

        //Assertion: The calculated value should conform to the ISO-8601 format for date and time representations.
        $value = $this->jobs[0]['timer'];
        $this->assertValidDate($value);

        //Force to dispatch the requersted job
        $this->dispatchJob();
        $this->assertEquals(1, $process->getInstances()->count());
    }

    /**
     * Test the start timer event with a time cycle specified by an expression.
     *
     */
    public function testStartTimerEventWithTimeCycleExpression()
    {
        //Load a BpmnFile Repository
        $bpmnRepository = new BpmnFileRepository();
        $bpmnRepository->setEngine($this->engine);
        $bpmnRepository->load(__DIR__ . '/files/Timer_StartEvent_TimeCycleExpression.bpmn');

        //Load a process from a bpmn repository by Id
        $process = $bpmnRepository->loadBpmElementById('Process');
        $startEvent = $bpmnRepository->loadBpmElementById('_9');

        //Assertion: The jobs manager receive a scheduling request to trigger the start event time cycle specified in the process
        $everyMinute = '1M';
        $calculatedCycle = 'R/PT' . $everyMinute;
        $this->assertScheduledCyclicTimer($calculatedCycle, $startEvent);

        //Assertion: The calculated value should conform to the ISO-8601 format for date and time representations.
        $value = $this->jobs[0]['timer'];
        $this->assertValidInterval($value);

        //Force to dispatch the requersted job
        $this->dispatchJob();
        $this->assertEquals(1, $process->getInstances()->count());

        //Force to dispatch the requersted job
        $this->dispatchJob();
        $this->assertEquals(2, $process->getInstances()->count());
    }

    /**
     * Validate if the string has a valid ISO8601 date time representation
     *
     * @param string $date
     */
    private function assertValidDate($date)
    {
        $this->assertTrue(new DateTime($date) !== false);
    }

    /**
     * Validate if the string has a valid ISO8601 interval (cycle or duration) representation
     *
     * @param string $interval
     */
    private function assertValidInterval($interval)
    {
        $this->assertTrue(new DateInterval($interval) !== false);
    }
}
