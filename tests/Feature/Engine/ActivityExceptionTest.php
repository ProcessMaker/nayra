<?php

namespace Tests\Feature\Engine;

use ProcessMaker\Nayra\Contracts\Bpmn\EventNodeInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\ActivityInterface;

/**
 * Test an activity with exception.
 *
 */
class ActivityExceptionTest extends EngineTestCase
{

    /**
     * Create a simple process
     *
     *     ┌────────┐
     *  ○─→│activity│─→●
     *     └────────┘
     *
     *
     * @return \ProcessMaker\Nayra\Contracts\Bpmn\ProcessInterface
     */
    private function createSimpleProcessInstance()
    {
        $process = $this->processRepository->createProcessInstance();
        //elements
        $start = $this->eventRepository->createStartEventInstance();
        $activity = $this->activityRepository->createActivityWithExceptionInstance();
        $end = $this->eventRepository->createEndEventInstance();
        $process->addActivity($activity);
        $process->addEvent($start)
            ->addEvent($end);
        //flows
        $start->createFlowTo($activity, $this->flowRepository);
        $activity->createFlowTo($end, $this->flowRepository);
        return $process;
    }

    /**
     * Test activity exception.
     *
     */
    public function testSimpleTransitions()
    {
        //Create a data store to test the process.
        $dataStore = $this->dataStoreRepository->createDataStoreInstance();

        //Load a simple process with activity exception.
        $process = $this->createSimpleProcessInstance();
        $this->engine->createExecutionInstance($process, $dataStore);

        //Get references to the start event and activity.
        $start = $process->getEvents()->item(0);
        $activity = $process->getActivities()->item(0);

        //Assert: Initially the activity does not have tokens.
        $this->assertEquals(0, $activity->getTokens($dataStore)->count());

        //Trigger start event
        $start->start();
        $this->engine->runToNextState();
        $this->assertEvents([
            EventNodeInterface::EVENT_EVENT_TRIGGERED,
            ActivityInterface::EVENT_ACTIVITY_ACTIVATED,
            ActivityInterface::EVENT_ACTIVITY_EXCEPTION,
        ]);

        //Assert: The activity has one token.
        $this->assertEquals(1, $activity->getTokens()->count());

        //Assert: The activity is in FAILING status.
        $token = $activity->getTokens()->item(0);
        $this->assertEquals(ActivityInterface::TOKEN_STATE_FAILING, $token->getStatus());

        //Complete the activity
        $token = $activity->getTokens()->item(0);
        $activity->complete($token);
        $this->engine->runToNextState();
        $this->assertEvents([
            ActivityInterface::EVENT_ACTIVITY_COMPLETED,
            ActivityInterface::EVENT_ACTIVITY_CLOSED,
            EventNodeInterface::EVENT_EVENT_TRIGGERED,
        ]);

        //Assert: Finally the activity does not have tokens.
        $this->assertEquals(0, $activity->getTokens($dataStore)->count());
    }
}
