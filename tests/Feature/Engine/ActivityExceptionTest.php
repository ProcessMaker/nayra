<?php

namespace Tests\Feature\Engine;

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
     * Test transitions from start event, activity and end event.
     *
     */
    public function testSimpleTransitions()
    {
        //Data store to access the runtime data.
        $dataStore = $this->dataStoreRepository->createDataStoreInstance();
        //Process contains the flow.
        $process = $this->createSimpleProcessInstance();
        $this->engine->createExecutionInstance($process, $dataStore);

        //Get References
        $start = $process->getEvents()->item(0);
        $activity = $process->getActivities()->item(0);

        $this->assertEquals(0, $activity->getTokens($dataStore)->count());
        //Raise start event
        $start->start();
        $this->engine->runToNextState();
        $this->assertEvents([
            'EventTriggered',
            'ActivityActivated',
            'ActivityException',
        ]);

        $this->assertEquals(1, $activity->getTokens($dataStore)->count());

        //Complete the activity
        $token = $activity->getTokens($dataStore)->item(0);
        $activity->complete($token);
        $this->engine->runToNextState();
        $this->assertEvents([
            'ActivityCompleted',
            'ActivityClosed',
            'EventTriggered',
        ]);

        $this->assertEquals(0, $activity->getTokens($dataStore)->count());
    }
}
