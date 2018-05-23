<?php

namespace Tests\Feature\Engine;

use ProcessMaker\Models\FormalExpression;
use ProcessMaker\Nayra\Contracts\Bpmn\ActivityInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\EventInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\IntermediateCatchEventInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\IntermediateTimerEventInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\ItemDefinitionInterface;
use ProcessMaker\Nayra\Contracts\Engine\JobManagerInterface;

class IntermediateTimerEventTest extends EngineTestCase
{
    /**
     * Creates a process with an intermediate timer event
     *
     * @return \ProcessMaker\Models\Process
     */
    public function createStartTimerEventProcess()
    {
        $process = $this->processRepository->createProcessInstance();

        $formalExpression = $this->rootElementRepository->createFormalExpressionInstance();
        $formalExpression->setId('formalExpression');
        $timerEventDefinition = $this->rootElementRepository->createTimerEventDefinitionInstance();
        $timerEventDefinition->setId("TimerEventDefinition");
        $timerEventDefinition->setTimeDuration($formalExpression);


        //elements
        $start = $this->eventRepository->createStartEventInstance();
        $timerEvent = $this->eventRepository->createIntermediateCatchEventInstance();
        $timerEvent->getEventDefinitions()->push($timerEventDefinition);
        $activityA = $this->activityRepository->createActivityInstance();
        $activityB = $this->activityRepository->createActivityInstance();

        $end = $this->eventRepository->createEndEventInstance();
        $process
            ->addActivity($activityA)
            ->addActivity($activityB);
        $process
            ->addEvent($start)
            ->addEvent($timerEvent)
            ->addEvent($end);

        //flows
        $start->createFlowTo($activityA, $this->flowRepository);
        $activityA->createFlowTo($timerEvent, $this->flowRepository);
        $timerEvent->createFlowTo($activityB, $this->flowRepository);
        $activityB->createFlowTo($end, $this->flowRepository);

        return $process;
    }


    public function testIntermediateTimerEvent()
    {
        //Create a data store with data.
        $dataStore = $this->dataStoreRepository->createDataStoreInstance();

        //Load the process
        $process = $this->createStartTimerEventProcess();
        $instance = $this->engine->createExecutionInstance($process, $dataStore);

        //Get References
        $start = $process->getEvents()->item(0);
        $activityA = $process->getActivities()->item(0);
        $activityB = $process->getActivities()->item(1);
        $timerEvent = $process->getEvents()->item(1);

        $instance = $this->engine->createExecutionInstance($process, $dataStore);

        //Start the process
        $start->start();

        $this->engine->runToNextState();

        //Assertion: Verify the triggered engine events. Two activities are activated.
        $this->assertEvents([
            EventInterface::EVENT_EVENT_TRIGGERED,
            ActivityInterface::EVENT_ACTIVITY_ACTIVATED
        ]);

        $token = $activityA->getTokens($instance)->item(0);
        $activityA->complete($token);
        $this->engine->runToNextState();

        //Assertion: Verify that the timer event send the schedule event
        $this->assertEvents([
            ActivityInterface::EVENT_ACTIVITY_COMPLETED,
            ActivityInterface::EVENT_ACTIVITY_CLOSED,
            IntermediateCatchEventInterface::EVENT_CATCH_TOKEN_ARRIVES,
            JobManagerInterface::EVENT_SCHEDULE_DURATION,
        ]);

        //timer event execution
        $timerEvent->execute($timerEvent->getEventDefinitions()->item(0), $instance);
        $this->engine->runToNextState();

        //Assertion: Verify that the timer event is triggered
        $this->assertEvents([
            IntermediateCatchEventInterface::EVENT_CATCH_TOKEN_CATCH,
            IntermediateCatchEventInterface::EVENT_CATCH_TOKEN_CONSUMED,
            IntermediateCatchEventInterface::EVENT_CATCH_TOKEN_PASSED,
            ActivityInterface::EVENT_ACTIVITY_ACTIVATED,
            EventInterface::EVENT_EVENT_TRIGGERED,
        ]);
    }
}
