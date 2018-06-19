<?php

namespace Tests\Feature\Engine;

use ProcessMaker\Nayra\Bpmn\ActivityTrait;
use ProcessMaker\Nayra\Bpmn\Models\ActivityActivatedEvent;
use ProcessMaker\Nayra\Contracts\Bpmn\ActivityInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\DataStoreInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\EndEventInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\EventInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\ProcessInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\StartEventInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\TokenInterface;

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
        $process = $this->factory->createProcess();
        //elements
        $start = $this->factory->createStartEvent();
        $activity = new ActivityWithException();
        $end = $this->factory->createEndEvent();
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
        $dataStore = $this->factory->createDataStore();

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
            ProcessInterface::EVENT_PROCESS_INSTANCE_CREATED,
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
            ProcessInterface::EVENT_PROCESS_INSTANCE_COMPLETED,
        ]);

        //Assertion: Finally the activity does not have tokens.
        $this->assertEquals(0, $activity->getTokens($instance)->count());
    }
}

class ActivityWithException implements ActivityInterface
{
    use ActivityTrait;

    /**
     * Configure the activity to go to a FAILING status when activated.
     *
     */
    protected function initActivity()
    {
        $this->attachEvent(ActivityInterface::EVENT_ACTIVITY_ACTIVATED, function ($self, TokenInterface $token) {
            $token->setStatus(ActivityInterface::TOKEN_STATE_FAILING);
        });
    }

    /**
     * Array map of custom event classes for the bpmn element.
     *
     * @return array
     */
    protected function getBpmnEventClasses()
    {
        return [
            ActivityInterface::EVENT_ACTIVITY_ACTIVATED => ActivityActivatedEvent::class,
        ];
    }
}
