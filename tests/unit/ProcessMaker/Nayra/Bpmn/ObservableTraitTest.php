<?php

namespace ProcessMaker\Nayra\Bpmn;

use PHPUnit\Framework\TestCase;
use ProcessMaker\Nayra\Bpmn\Models\InclusiveGateway;
use ProcessMaker\Nayra\Contracts\Bpmn\TransitionInterface;

class ObservableTraitTest extends TestCase
{
    /**
     * Dummy function to test if a callback function is attached/detached
     */
    public function dummyFunction()
    {
        return 'dummy';
    }

    /**
     * Tests that a callback function used to observe an entity is attached/detached correctly
     */
    public function testAttachDetach()
    {
        $dummyGateway = new InclusiveGateway();

        //The activity transition will be the object to observe
        $transition = new ActivityCompletedTransition($dummyGateway);

        //Assertion: once attached to an event the observer count should be incremented by one
        $transition->attachEvent(TransitionInterface::EVENT_AFTER_CONSUME, [$this,'dummyFunction']);
        $this->assertCount(1, $transition->getObservers()[TransitionInterface::EVENT_AFTER_CONSUME]);

        //Assertion: once attached to an event the observer count should be reduced by one
        $transition->detachEvent(TransitionInterface::EVENT_AFTER_CONSUME, [$this, 'dummyFunction']);
        $this->assertCount(0, $transition->getObservers()[TransitionInterface::EVENT_AFTER_CONSUME]);
    }
}
