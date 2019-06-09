<?php

namespace Tests\Feature\Engine;

use ProcessMaker\Nayra\Contracts\Bpmn\ActivityInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\DiagramInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\EndEventInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\ProcessInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\StartEventInterface;
use ProcessMaker\Nayra\Exceptions\InvalidSequenceFlowException;
use ProcessMaker\Nayra\Contracts\Bpmn\EventInterface;

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
        $process = $this->repository->createProcess();
        //elements
        $start = $this->repository->createStartEvent();
        $activity = $this->repository->createActivity();
        $end = $this->repository->createEndEvent();
        $process->addActivity($activity);
        $process->addEvent($start)
            ->addEvent($end);
        //flows
        $start->createFlowTo($activity, $this->repository);
        $activity->createFlowTo($end, $this->repository);
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
        $dataStore = $this->repository->createDataStore();
        //Load the process
        $process = $this->createSimpleProcessInstance();
        //Create a process instance with the data store
        $instance = $this->engine->createExecutionInstance($process, $dataStore);

        //Get references to start event and activity
        $start = $process->getEvents()->item(0);
        $activity = $process->getActivities()->item(0);

        //Assertion: Verify the activity has no tokens
        $this->assertEquals(0, $activity->getTokens($instance)->count());

        //Trigger start event
        $start->start($instance);
        $this->engine->runToNextState();
        $this->assertEvents([
            ProcessInterface::EVENT_PROCESS_INSTANCE_CREATED,
            StartEventInterface::EVENT_EVENT_TRIGGERED,
            ActivityInterface::EVENT_ACTIVITY_ACTIVATED,
        ]);

        //Assertion: Verify the activity has one token
        $this->assertEquals(1, $activity->getTokens($instance)->count());

        //Get the current token
        $token = $activity->getTokens($instance)->item(0);

        //Assertion: Verify the token refers to the activity
        $this->assertEquals($activity, $token->getOwnerElement());

        //Complete the activity
        $activity->complete($token);
        $this->engine->runToNextState();

        //Assertion: Verify the close events
        $this->assertEvents([
            ActivityInterface::EVENT_ACTIVITY_COMPLETED,
            ActivityInterface::EVENT_ACTIVITY_CLOSED,
            EndEventInterface::EVENT_THROW_TOKEN_ARRIVES,
            EndEventInterface::EVENT_THROW_TOKEN_CONSUMED,
            EndEventInterface::EVENT_EVENT_TRIGGERED,
            ProcessInterface::EVENT_PROCESS_INSTANCE_COMPLETED,
        ]);

        //Assertion: Verify the activity has no tokens
        $this->assertEquals(0, $activity->getTokens($instance)->count());
    }

    /**
     * Tests that a process structure has been configured correctly
     */
    public function testProcessConfiguration()
    {
        //Create a data store
        $dataStore = $this->repository->createDataStore();

        //Load the process
        $process = $this->createSimpleProcessInstance();

        //Create a process instance with the data store
        $instance = $this->engine->createExecutionInstance($process, $dataStore);

        //Get references to start event and activity
        $start = $process->getEvents()->item(0);
        $activity = $process->getActivities()->item(0);
        $end = $process->getEvents()->item(1);

        //Assertion: no tokens are returned from the end event
        $this->assertCount(0, $end->getTokens($instance));

        //Assertion: neither targets nor origins should be null
        $this->assertNotNull($start->getTransitions()[0]->outgoing()->item(0)->target());
        $this->assertNotNull($start->getTransitions()[0]->outgoing()->item(0)->origin());

        //Assertion: the start event should not have tokens
        $this->assertCount(0, $start->getTokens($instance));

        //Assertion: the set/get methods of the diagram should work
        $diagram = $this->getMockForAbstractClass(DiagramInterface::class);
        $process->setDiagram($diagram);
        $this->assertEquals($diagram, $process->getDiagram());
    }

    /**
     * Tests that a process structure has been configured incorrectly
     */
    public function testProcessIncorrectConfiguration()
    {
        //Create a data store
        $dataStore = $this->repository->createDataStore();

        //Load the process
        $process = $this->createSimpleProcessInstance();

        //Get reference to end event and activity
        $end = $process->getEvents()->item(1);
        $activity = $process->getActivities()->item(0);

        //Try to add and invalid flow to the end event
        try {
            $end->createFlowTo($activity, $this->repository);
            $this->engine->createExecutionInstance($process, $dataStore);
        } catch (InvalidSequenceFlowException $e) {
            $this->assertNotNull($e->getMessage());
        }
    }

    /**
     * Tests that when a script fails, then it is closed
     */
    public function testCloseDirectlyActiveTask()
    {
        //Load a process
        $process = $this->createSimpleProcessInstance();
        $dataStore = $this->repository->createDataStore();

        //create an instance of the process
        $instance = $this->engine->createExecutionInstance($process, $dataStore);

        //Get References
        $start = $process->getEvents()->item(0);

        //start the process an instance of the process
        $start->start($instance);
        $this->engine->runToNextState();

        //Assert: that the process is stared and the first activity activated
        $this->assertEvents([
            ProcessInterface::EVENT_PROCESS_INSTANCE_CREATED,
            EventInterface::EVENT_EVENT_TRIGGERED,
            ActivityInterface::EVENT_ACTIVITY_ACTIVATED,
        ]);

        //close the process instance
        $instance->close();
        $this->engine->runToNextState();

        //Assertion: Verify that the proces instance was completed
        $this->assertEvents([
            ActivityInterface::EVENT_EVENT_TRIGGERED,
            ProcessInterface::EVENT_PROCESS_INSTANCE_COMPLETED,
        ]);
    }
}
