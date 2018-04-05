<?php

namespace Tests\Feature\Engine;

use ProcessMaker\Nayra\Contracts\Bpmn\DiagramInterface;
use ProcessMaker\Nayra\Exceptions\InvalidSequenceFlowException;

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

        //Assertion: Verify the activity has no tokens
        $this->assertEquals(0, $activity->getTokens($dataStore)->count());

        //Trigger start event
        $start->start();
        $this->engine->runToNextState();
        $this->assertEvents([
            'EventTriggered',
            'ActivityActivated',
        ]);

        //Assertion: Verify the activity has one token
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

        //Assertion: Verify the activity has no tokens
        $this->assertEquals(0, $activity->getTokens($dataStore)->count());
    }

    /**
     * Tests that a process structure has been configured correctly
     */
    public function testProcessConfiguration()
    {
        //Create a data store
        $dataStore = $this->dataStoreRepository->createDataStoreInstance();

        //Load the process
        $process = $this->createSimpleProcessInstance();

        //Create a process instance with the data store
        $instance = $this->engine->createExecutionInstance($process, $dataStore);

        //Get references to start event and activity
        $start = $process->getEvents()->item(0);
        $activity = $process->getActivities()->item(0);
        $end = $process->getEvents()->item(1);

        //Assertion: no tokens are returned from the end event
        $this->assertCount(0, $end->getTokens($dataStore));

        //Assertion: neither targets nor origins should be null
        $this->assertNotNull($start->getTransitions()[0]->outgoing()->item(0)->target());
        $this->assertNotNull($start->getTransitions()[0]->outgoing()->item(0)->origin());

        //Assertion: the start event should not have tokens
        $this->assertCount(0, $start->getTokens($dataStore));

        //Try to add and invalid flow to the end event
        try {
            $end->createFlowTo($activity, $this->flowRepository);
            $this->engine->createExecutionInstance($process, $dataStore);
        }
        catch (InvalidSequenceFlowException $e) {
            $this->assertNotNull($e->getMessage());
        }

        //Assertion: the set/get methods of the diagram should work
        $diagram = $this->getMockForAbstractClass(DiagramInterface::class);
        $process->setDiagram($diagram);
        $this->assertEquals($diagram, $process->getDiagram());
    }
}

