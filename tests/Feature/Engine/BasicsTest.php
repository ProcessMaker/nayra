<?php

namespace Tests\Feature\Engine;

/**
 * Test transitions
 *
 */
class BasicsTest extends EngineTestCase
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
        $activity = $this->activityRepository->createActivityInstance();
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
     * Sequence flow
     * 
     * Test transitions between start event, activity and end event.
     *
     */
    public function testSimpleTransitions()
    {
        //Create a data store
        $dataStore = $this->dataStoreRepository->createDataStoreInstance();
        //Load the process
        $process = $this->createSimpleProcessInstance();
        //Create a process instance with the data store
        $this->engine->createExecutionInstance($process, $dataStore);

        //Get references to start event and activity
        $start = $process->getEvents()->item(0);
        $activity = $process->getActivities()->item(0);

        $this->assertEquals(0, $activity->getTokens($dataStore)->count());
        //Raise start event
        $start->start();
        $this->engine->runToNextState();
        $this->assertEvents([
            'EventTriggered',
            'ActivityActivated',
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
