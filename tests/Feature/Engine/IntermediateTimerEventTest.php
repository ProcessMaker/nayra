<?php

namespace Tests\Feature\Engine;

use ProcessMaker\Nayra\Contracts\Bpmn\ActivityInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\DataStoreInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\EndEventInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\EventInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\IntermediateCatchEventInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\ProcessInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\StartEventInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\TimerEventDefinitionInterface;
use ProcessMaker\Nayra\Contracts\Engine\JobManagerInterface;
use ProcessMaker\Test\Models\FormalExpression;

/**
 * Test intermediate timer events
 */
class IntermediateTimerEventTest extends EngineTestCase
{
    /**
     * Creates a process with an intermediate timer event
     *
     * @return \ProcessMaker\Models\Process
     */
    public function createStartTimerEventProcess()
    {
        $process = $this->repository->createProcess();
        $process->setEngine($this->engine);

        //elements
        $start = $this->repository->createStartEvent();
        $timerEvent = $this->repository->createIntermediateCatchEvent();
        $activityA = $this->repository->createActivity();
        $activityB = $this->repository->createActivity();
        $end = $this->repository->createEndEvent();

        $process
            ->addActivity($activityA)
            ->addActivity($activityB);
        $process
            ->addEvent($start)
            ->addEvent($timerEvent)
            ->addEvent($end);

        //flows
        $start->createFlowTo($activityA, $this->repository);
        $activityA->createFlowTo($timerEvent, $this->repository);
        $timerEvent->createFlowTo($activityB, $this->repository);
        $activityB->createFlowTo($end, $this->repository);

        return $process;
    }


    /**
     * Tests that a intermediate timer event that uses duration, sends the events to schedules the job
     */
    public function testIntermediateTimerEventWithDuration()
    {
        //Create a data store with data.
        $dataStore = $this->repository->createDataStore();

        //Load the process
        $process = $this->createStartTimerEventProcess();

        //Get references to the process elements
        $start = $process->getEvents()->item(0);
        $activityA = $process->getActivities()->item(0);
        $activityB = $process->getActivities()->item(1);
        $timerEvent = $process->getEvents()->item(1);

        $this->addTimerEventDefinition($timerEvent, "duration");

        //create an instance of the process
        $instance = $this->engine->createExecutionInstance($process, $dataStore);

        //run the process
        $start->start();
        $this->engine->runToNextState();

        //Complete the first task
        $token = $activityA->getTokens($instance)->item(0);
        $activityA->complete($token);
        $this->engine->runToNextState();

        //Assertion: one token should arrive to the intermediate timer event
        $this->assertTrue($timerEvent->getTokens($instance)->count() === 1);

        //Assertion: verify that the event schedule duration is sent to the job manager
        $this->assertEvents([
            ProcessInterface::EVENT_PROCESS_INSTANCE_CREATED,
            EventInterface::EVENT_EVENT_TRIGGERED,
            ActivityInterface::EVENT_ACTIVITY_ACTIVATED,
            ActivityInterface::EVENT_ACTIVITY_COMPLETED,
            ActivityInterface::EVENT_ACTIVITY_CLOSED,
            IntermediateCatchEventInterface::EVENT_CATCH_TOKEN_ARRIVES,
            JobManagerInterface::EVENT_SCHEDULE_DURATION,
        ]);

        //force the dispatch of the required job simulation and execute call
        $timerEvent->execute($timerEvent->getEventDefinitions()->item(0), $instance);
        $this->engine->runToNextState();

        //Assertion: the process should continue to the next task
        $this->assertEvents([
            IntermediateCatchEventInterface::EVENT_CATCH_MESSAGE_CATCH,
            IntermediateCatchEventInterface::EVENT_CATCH_TOKEN_CONSUMED,
            IntermediateCatchEventInterface::EVENT_CATCH_MESSAGE_CONSUMED,
            IntermediateCatchEventInterface::EVENT_CATCH_TOKEN_PASSED,
            EventInterface::EVENT_EVENT_TRIGGERED,
            ActivityInterface::EVENT_ACTIVITY_ACTIVATED,
        ]);
    }

    /**
     * Tests that a intermediate timer event that uses cycles, sends the events to schedules the job
     */
    public function testIntermediateTimerEventWithCycle()
    {
        //Create a data store with data.
        $dataStore = $this->repository->createDataStore();

        //Load the process
        $process = $this->createStartTimerEventProcess();

        //Get references to the process elements
        $start = $process->getEvents()->item(0);
        $activityA = $process->getActivities()->item(0);
        $activityB = $process->getActivities()->item(1);
        $timerEvent = $process->getEvents()->item(1);

        $this->addTimerEventDefinition($timerEvent, "cycle");

        //create an instance of the process
        $instance = $this->engine->createExecutionInstance($process, $dataStore);

        //run the process
        $start->start();
        $this->engine->runToNextState();

        //Complete the first task
        $token = $activityA->getTokens($instance)->item(0);
        $activityA->complete($token);
        $this->engine->runToNextState();

        //Assertion: one token should arrive to the intermediate timer event
        $this->assertTrue($timerEvent->getTokens($instance)->count() === 1);

        //Assertion: verify that the event schedule duration is sent to the job manager
        $this->assertEvents([
            ProcessInterface::EVENT_PROCESS_INSTANCE_CREATED,
            EventInterface::EVENT_EVENT_TRIGGERED,
            ActivityInterface::EVENT_ACTIVITY_ACTIVATED,
            ActivityInterface::EVENT_ACTIVITY_COMPLETED,
            ActivityInterface::EVENT_ACTIVITY_CLOSED,
            IntermediateCatchEventInterface::EVENT_CATCH_TOKEN_ARRIVES,
            JobManagerInterface::EVENT_SCHEDULE_CYCLE,
        ]);

        //force the dispatch of the required job simulation and execute call
        $timerEvent->execute($timerEvent->getEventDefinitions()->item(0), $instance);
        $this->engine->runToNextState();

        //Assertion: the process should continue to the next task
        $this->assertEvents([
            IntermediateCatchEventInterface::EVENT_CATCH_MESSAGE_CATCH,
            IntermediateCatchEventInterface::EVENT_CATCH_TOKEN_CONSUMED,
            IntermediateCatchEventInterface::EVENT_CATCH_MESSAGE_CONSUMED,
            IntermediateCatchEventInterface::EVENT_CATCH_TOKEN_PASSED,
            EventInterface::EVENT_EVENT_TRIGGERED,
            ActivityInterface::EVENT_ACTIVITY_ACTIVATED,
        ]);
    }

    /**
     * Tests that a intermediate timer event that uses dates, sends the events to schedules the job
     */
    public function testIntermediateTimerEventWithDate()
    {
        //Create a data store with data.
        $dataStore = $this->repository->createDataStore();

        //Load the process
        $process = $this->createStartTimerEventProcess();

        //Get references to the process elements
        $start = $process->getEvents()->item(0);
        $activityA = $process->getActivities()->item(0);
        $activityB = $process->getActivities()->item(1);
        $timerEvent = $process->getEvents()->item(1);

        $this->addTimerEventDefinition($timerEvent, "date");

        //create an instance of the process
        $instance = $this->engine->createExecutionInstance($process, $dataStore);

        //run the process
        $start->start();
        $this->engine->runToNextState();

        //Complete the first task
        $token = $activityA->getTokens($instance)->item(0);
        $activityA->complete($token);
        $this->engine->runToNextState();

        //Assertion: one token should arrive to the intermediate timer event
        $this->assertTrue($timerEvent->getTokens($instance)->count() === 1);

        //Assertion: verify that the event schedule duration is sent to the job manager
        $this->assertEvents([
            ProcessInterface::EVENT_PROCESS_INSTANCE_CREATED,
            EventInterface::EVENT_EVENT_TRIGGERED,
            ActivityInterface::EVENT_ACTIVITY_ACTIVATED,
            ActivityInterface::EVENT_ACTIVITY_COMPLETED,
            ActivityInterface::EVENT_ACTIVITY_CLOSED,
            IntermediateCatchEventInterface::EVENT_CATCH_TOKEN_ARRIVES,
            JobManagerInterface::EVENT_SCHEDULE_DATE,
        ]);

        //force the dispatch of the required job simulation and execute call
        $timerEvent->execute($timerEvent->getEventDefinitions()->item(0), $instance);
        $this->engine->runToNextState();

        //Assertion: the process should continue to the next task
        $this->assertEvents([
            IntermediateCatchEventInterface::EVENT_CATCH_MESSAGE_CATCH,
            IntermediateCatchEventInterface::EVENT_CATCH_TOKEN_CONSUMED,
            IntermediateCatchEventInterface::EVENT_CATCH_MESSAGE_CONSUMED,
            IntermediateCatchEventInterface::EVENT_CATCH_TOKEN_PASSED,
            EventInterface::EVENT_EVENT_TRIGGERED,
            ActivityInterface::EVENT_ACTIVITY_ACTIVATED,
        ]);
    }


    /**
     * Adds a test timer event definition for the timer event passed
     *
     * @param EventInterface $timerEvent
     * @param string $type
     *
     * @return \ProcessMaker\Nayra\Bpmn\Models\TimerEventDefinition
     */
    private function addTimerEventDefinition (EventInterface $timerEvent, $type)
    {
        $formalExpression = new FormalExpression();
        $formalExpression->setId('formalExpression');

        $timerEventDefinition = $this->repository->createTimerEventDefinition();

        $timerEventDefinition->setId("TimerEventDefinition");
        switch ($type) {
            case "duration":
                $timerEventDefinition->setTimeDuration(function ($data) {return '1H';});
                break;
            case "cycle":
                $timerEventDefinition->setTimeCycle(function ($data) { return 'R4/2018-05-01T00:00:00Z/PT1M';});
                break;
            default:
                $timerEventDefinition->setTimeDate(function ($data) {return '2018-05-01T14:30:00';});
        }

        $timerEvent->getEventDefinitions()->push($timerEventDefinition);
        return $timerEventDefinition;
    }
}
