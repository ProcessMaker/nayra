<?php

namespace Tests\Feature\Engine;

use DateInterval;
use DatePeriod;
use DateTime;
use Exception;
use ProcessMaker\Repositories\BpmnFileRepository;

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
        $bpmnRepository = new BpmnFileRepository();
        $bpmnRepository->setEngine($this->engine);
        $bpmnRepository->load(__DIR__ . '/files/Timer_StartEvent_TimeDate.bpmn');

        //Load a process from a bpmn repository by Id
        $process = $bpmnRepository->loadBpmElementById('Process');
        $startEvent = $bpmnRepository->loadBpmElementById('_9');
        $this->engine->loadProcess($process);

        //Assertion: The jobs manager receive a scheduling request to trigger the start event time cycle specified in the process
        $this->assertScheduledDateTimer('2018-05-01T14:30:00', $startEvent);

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
        $this->engine->loadProcess($process);

        //Assertion: The jobs manager receive a scheduling request to trigger the start event time cycle specified in the process
        $this->assertScheduledCyclicTimer('R4/2018-05-01T00:00:00Z/PT1M', $startEvent);

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

        //Create a default environment data
        $environmentData = $bpmnRepository->getDataStoreRepository()->createDataStoreInstance();
        $this->engine->setDataStore($environmentData);
        $date = new DateTime;
        $date->setTime(23, 59, 59);
        $calculatedDate = $date->format(DateTime::ISO8601);
        $environmentData->putData('calculatedDate', $calculatedDate);

        //Load a process from a bpmn repository by Id
        $process = $bpmnRepository->loadBpmElementById('Process');
        $startEvent = $bpmnRepository->loadBpmElementById('_9');
        $this->engine->loadProcess($process);

        //Assertion: The jobs manager receive a scheduling request to trigger the start event at the date time calculated by the expression
        $this->assertScheduledDateTimer($calculatedDate, $startEvent);

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

        //Create a default environment data
        $environmentData = $bpmnRepository->getDataStoreRepository()->createDataStoreInstance();
        $this->engine->setDataStore($environmentData);
        $everyMinute = '1M';
        $calculatedCycle = 'R4/2018-05-01T00:00:00Z/PT' . $everyMinute;
        $environmentData->putData('calculatedCycle', $calculatedCycle);

        //Load a process from a bpmn repository by Id
        $process = $bpmnRepository->loadBpmElementById('Process');
        $startEvent = $bpmnRepository->loadBpmElementById('_9');
        $this->engine->loadProcess($process);

        //Assertion: The jobs manager receive a scheduling request to trigger the start event time cycle specified in the process
        $this->assertScheduledCyclicTimer($calculatedCycle, $startEvent);

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
     * @param string $expression
     */
    private function assertValidDate($expression)
    {
        try {
            $date = new DateTime($expression);
        } catch (Exception $e) {
            $date = false;
        }
        $this->assertTrue($date !== false, "Failed asserting that $expression is a valid date timer");

    }

    /**
     * Validate if the string has a valid ISO8601 cycle representation
     *
     * @param string $expression
     */
    private function assertValidCycle($expression)
    {
        try {
            $cycle = new DatePeriod($expression);
        } catch (Exception $e) {
            $cycle = false;
        }
        $this->assertTrue($cycle !== false, "Failed asserting that $expression is a valid cyclic timer");
    }

    /**
     * Validate if the string has a valid ISO8601 duration representation
     *
     * @param string $expression
     */
    private function assertValidDuration($expression)
    {
        try {
            $duration = new DateInterval($expression);
        } catch (Exception $e) {
            $duration = false;
        }
        $this->assertTrue($duration !== false, "Failed asserting that $expression is a valid duration timer");
    }
}
