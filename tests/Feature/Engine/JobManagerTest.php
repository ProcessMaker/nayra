<?php

namespace Tests\Feature\Engine;

use DateInterval;
use DateTime;
use ProcessMaker\Nayra\Engine\JobManagerTrait;
use ProcessMaker\Nayra\Storage\BpmnDocument;

/**
 * Start Timer Event tests
 */
class JobManagerTest extends EngineTestCase
{
    use JobManagerTrait;

    /**
     * Test the job manager date time cycle calculation.
     */
    public function testGetNextDateTimeCycle()
    {
        /* @var $startDate \DateTime */
        /* @var $interval \DateInterval */

        //Load a BpmnFile Repository
        $bpmnRepository = new BpmnDocument();
        $bpmnRepository->setEngine($this->engine);
        $bpmnRepository->setFactory($this->repository);
        $bpmnRepository->load(__DIR__.'/files/Timer_StartEvent_TimeCycle.bpmn');

        //Load a process from a bpmn repository by Id
        $process = $bpmnRepository->getProcess('Process');
        $this->engine->loadProcess($process);

        //Get scheduled job information
        $job = $this->jobs[0];
        $startDate = $job['timer']->getStartDate();
        $interval = $job['timer']->getDateInterval();

        //Get the next DateTime cycle after the start date of the timer.
        $nextDate = $this->getNextDateTimeCycle($job['timer'], $startDate);

        //Assertion: The next DateTime must be the start date plus one interval.
        $expected = clone $startDate;
        $expected->add($interval);
        $this->assertEquals($expected, $nextDate);

        //Get the next DateTime cycle
        $nextDateTwo = $this->getNextDateTimeCycle($job['timer'], $expected);

        //Assertion: The next DateTime must be the start date plus two intervals.
        $expectedNext = clone $startDate;
        $expectedNext->add($interval);
        $expectedNext->add($interval);
        $this->assertEquals($expectedNext, $nextDateTwo);
    }

    /**
     * Test the job manager date time cycle between two dates.
     */
    public function testCycleBetweenDatesExpression()
    {
        /* @var $startDate \DateTime */
        /* @var $interval \DateInterval */

        //Load a BpmnFile Repository
        $bpmnRepository = new BpmnDocument();
        $bpmnRepository->setEngine($this->engine);
        $bpmnRepository->setFactory($this->repository);
        $bpmnRepository->load(__DIR__.'/files/Timer_StartEvent_TimeCycle.bpmn');

        //Load a process from a bpmn repository by Id
        $process = $bpmnRepository->getProcess('Process');
        $timerEventDefinition = $bpmnRepository->getTimerEventDefinition('_9_ED_1');

        //Set a time cycle between two dates
        $expression = $this->repository->createFormalExpression();
        $expression->setRepository($this->repository);
        $expression->setProperty('body', 'R/2018-05-01T00:00:00Z/PT1M/2018-06-01T00:00:00Z');
        $timerEventDefinition->setTimeCycle($expression);

        $this->engine->loadProcess($process);

        //Get scheduled job information
        $job = $this->jobs[0];
        $startDate = $job['timer']->getStartDate();
        $interval = $job['timer']->getDateInterval();

        //Get the next DateTime cycle after the start date of the timer.
        $nextDate = $this->getNextDateTimeCycle($job['timer'], $startDate);

        //Assertion: The next DateTime must be the start date plus one interval.
        $expected = clone $startDate;
        $expected->add($interval);
        $this->assertEquals($expected, $nextDate);

        //Get the next DateTime cycle
        $nextDateTwo = $this->getNextDateTimeCycle($job['timer'], $expected);

        //Assertion: The next DateTime must be the start date plus two intervals.
        $expectedNext = clone $startDate;
        $expectedNext->add($interval);
        $expectedNext->add($interval);
        $this->assertEquals($expectedNext, $nextDateTwo);
    }

    /**
     * Test the job manager with timer event with an specify date.
     */
    public function testSpecificDate()
    {
        //Load a BpmnFile Repository
        $bpmnRepository = new BpmnDocument();
        $bpmnRepository->setEngine($this->engine);
        $bpmnRepository->setFactory($this->repository);
        $bpmnRepository->load(__DIR__.'/files/Timer_StartEvent_TimeDate.bpmn');

        //Load a process from a bpmn repository by Id
        $process = $bpmnRepository->getProcess('Process');
        $timerEventDefinition = $bpmnRepository->getTimerEventDefinition('_9_ED_1');

        //Set an specific date time
        $date = '2018-10-02T21:30:00Z';
        $expression = $this->repository->createFormalExpression();
        $expression->setRepository($this->repository);
        $expression->setProperty('body', $date);
        $timerEventDefinition->setTimeDate($expression);

        $this->engine->loadProcess($process);

        //Get scheduled job information
        $job = $this->jobs[0];
        $startDate = $job['timer'];

        //Assertion: The start DateTime must be the one specified.
        $expected = new DateTime($date);
        $this->assertEquals($expected, $startDate);
    }

    /**
     * Test the job manager with timer event with an date time interval.
     */
    public function testDateTimeInterval()
    {
        //Load a BpmnFile Repository
        $bpmnRepository = new BpmnDocument();
        $bpmnRepository->setEngine($this->engine);
        $bpmnRepository->setFactory($this->repository);
        $bpmnRepository->load(__DIR__.'/files/Timer_StartEvent_TimeDateInterval.bpmn');

        //Load a process from a bpmn repository by Id
        $process = $bpmnRepository->getProcess('Process');
        $timerEventDefinition = $bpmnRepository->getTimerEventDefinition('_9_ED_1');

        //Set an date time interval
        $interval = 'PT1H';
        $expression = $this->repository->createFormalExpression();
        $expression->setRepository($this->repository);
        $expression->setProperty('body', $interval);
        $timerEventDefinition->setTimeDuration($expression);

        $this->engine->loadProcess($process);

        //Get scheduled job information
        $job = $this->jobs[0];
        $duration = $job['timer'];

        //Assertion: The duration must be the specified date time interval.
        $expected = new DateInterval($interval);
        $this->assertEquals($expected, $duration);
    }
}
