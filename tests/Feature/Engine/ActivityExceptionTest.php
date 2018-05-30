<?php

namespace Tests\Feature\Engine;

use ProcessMaker\Nayra\Contracts\Bpmn\ActivityInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\EndEventInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\EventInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\ProcessInterface;

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
        $start->createFlowTo($activity, $this->factory);
        $activity->createFlowTo($end, $this->factory);
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
        $instance = $this->engine->createExecutionInstance($process, $dataStore);

        //Get references to the start event and activity.
        $start = $process->getEvents()->item(0);
        $activity = $process->getActivities()->item(0);

        //Assertion: Initially the activity does not have tokens.
        $this->assertEquals(0, $activity->getTokens($instance)->count());

        //Trigger start event
        $start->start();
        $this->engine->runToNextState();
        $this->assertEvents([
            EventInterface::EVENT_EVENT_TRIGGERED,
            ActivityInterface::EVENT_ACTIVITY_ACTIVATED,
            ActivityInterface::EVENT_ACTIVITY_EXCEPTION,
        ]);

        //Assertion: The activity has one token.
        $this->assertEquals(1, $activity->getTokens($instance)->count());

        //Assertion: The activity is in FAILING status.
        $token = $activity->getTokens($instance)->item(0);
        $this->assertEquals(ActivityInterface::TOKEN_STATE_FAILING, $token->getOwnerStatus());

        //Complete the activity
        $token = $activity->getTokens($instance)->item(0);
        $activity->complete($token);
        $this->engine->runToNextState();
        $this->assertEvents([
            ActivityInterface::EVENT_ACTIVITY_COMPLETED,
            ActivityInterface::EVENT_ACTIVITY_CLOSED,
            EndEventInterface::EVENT_THROW_TOKEN_ARRIVES,
            EndEventInterface::EVENT_THROW_TOKEN_CONSUMED,
            EndEventInterface::EVENT_EVENT_TRIGGERED,
            ProcessInterface::EVENT_PROCESS_COMPLETED,
        ]);

        //Assertion: Finally the activity does not have tokens.
        $this->assertEquals(0, $activity->getTokens($instance)->count());
    }
}
