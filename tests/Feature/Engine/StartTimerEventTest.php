<?php

namespace Tests\Feature\Engine;

use DateInterval;
use DatePeriod;
use DateTime;
use ProcessMaker\Nayra\Storage\BpmnDocument;

/**
 * Start Timer Event tests
 *
 */
class StartTimerEventTest extends EngineTestCase
{

    /**
     * Test the start timer event with a date time specified in ISO8601 format.
     *
     */
    public function testStartTimerEventWithTimeDate()
    {
        //Load a BpmnFile Repository
        $bpmnRepository = new BpmnDocument();
        $bpmnRepository->setEngine($this->engine);
        $bpmnRepository->setFactory($this->repository);
        $bpmnRepository->load(__DIR__ . '/files/Timer_StartEvent_TimeDate.bpmn');

        //Load a process from a bpmn repository by Id
        $process = $bpmnRepository->getProcess('Process');
        $startEvent = $bpmnRepository->getStartEvent('_9');
        $this->engine->loadProcess($process);

        //Assertion: The jobs manager receive a scheduling request to trigger the start event time cycle specified in the process
        $this->assertScheduledDateTimer(new DateTime('2018-05-01T14:30:00'), $startEvent);

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
        $bpmnRepository = new BpmnDocument();
        $bpmnRepository->setEngine($this->engine);
        $bpmnRepository->setFactory($this->repository);
        $bpmnRepository->load(__DIR__ . '/files/Timer_StartEvent_TimeCycle.bpmn');

        //Load a process from a bpmn repository by Id
        $process = $bpmnRepository->getProcess('Process');
        $startEvent = $bpmnRepository->getStartEvent('_9');
        $this->engine->loadProcess($process);

        //Assertion: The jobs manager receive a scheduling request to trigger the start event time cycle specified in the process
        $this->assertScheduledCyclicTimer(new DatePeriod('R4/2018-05-01T00:00:00Z/PT1M'), $startEvent);

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
        $bpmnRepository = new BpmnDocument();
        $bpmnRepository->setEngine($this->engine);
        $bpmnRepository->setFactory($this->repository);
        $bpmnRepository->load(__DIR__ . '/files/Timer_StartEvent_TimeDateExpression.bpmn');

        //Create a default environment data
        $environmentData = $this->repository->createDataStore();
        $this->engine->setDataStore($environmentData);
        $date = new DateTime;
        $date->setTime(23, 59, 59);
        $calculatedDate = $date->format(DateTime::ISO8601);
        $environmentData->putData('calculatedDate', $calculatedDate);

        //Load a process from a bpmn repository by Id
        $process = $bpmnRepository->getProcess('Process');
        $startEvent = $bpmnRepository->getStartEvent('_9');
        $this->engine->loadProcess($process);

        //Assertion: The jobs manager receive a scheduling request to trigger the start event at the date time calculated by the expression
        $this->assertScheduledDateTimer(new DateTime($calculatedDate), $startEvent);

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
        $bpmnRepository = new BpmnDocument();
        $bpmnRepository->setEngine($this->engine);
        $bpmnRepository->setFactory($this->repository);
        $bpmnRepository->load(__DIR__ . '/files/Timer_StartEvent_TimeCycleExpression.bpmn');

        //Create a default environment data
        $environmentData = $this->repository->createDataStore();

        $this->engine->setDataStore($environmentData);

        //Calculate a iso8601 string for a cyclic timer of 1 minute from 2018-05-01 at 00:00 UTC
        $everyMinute = '1M';
        $fromDate = '2018-05-01T00:00:00Z';
        $calculatedCycle = 'R4/' . $fromDate . '/PT' . $everyMinute;
        $environmentData->putData('calculatedCycle', $calculatedCycle);

        //Load a process from a bpmn repository by Id
        $process = $bpmnRepository->getProcess('Process');
        $startEvent = $bpmnRepository->getStartEvent('_9');
        $this->engine->loadProcess($process);

        //Assertion: The jobs manager receive a scheduling request to trigger the start event time cycle specified in the process
        $this->assertScheduledCyclicTimer(new \DatePeriod($calculatedCycle), $startEvent);

        //Assertion: The calculated value should conform to the ISO-8601 format for date and time representations.
        $value = $this->jobs[0]['timer'];
        $this->assertValidCycle($value);

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
     * @param mixed $date
     */
    private function assertValidDate($date)
    {
        $this->assertTrue($date instanceof DateTime, "Failed asserting that ".json_encode($date)." is a valid date timer");

    }

    /**
     * Validate if the string has a valid ISO8601 cycle representation
     *
     * @param mixed $cycle
     */
    private function assertValidCycle($cycle)
    {
        $this->assertTrue($cycle instanceof DatePeriod, "Failed asserting that ".json_encode($cycle)." is a valid cyclic timer");
    }

    /**
     * Validate if the string has a valid ISO8601 duration representation
     *
     * @param mixed $duration
     */
    private function assertValidDuration($duration)
    {
        $this->assertTrue($duration instanceof DateInterval, "Failed asserting that ".json_encode($duration)." is a valid duration timer");
    }
}
