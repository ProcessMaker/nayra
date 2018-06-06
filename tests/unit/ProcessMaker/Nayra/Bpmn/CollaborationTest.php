<?php

namespace ProcessMaker\Nayra\Bpmn;

use PHPUnit\Framework\Assert;
use PHPUnit\Framework\TestCase;
use ProcessMaker\Nayra\Bpmn\Model\StartEvent;
use ProcessMaker\Nayra\Bpmn\Models\Collaboration;
use ProcessMaker\Nayra\Bpmn\Models\MessageFlow;

class CollaborationTest extends TestCase
{
    /**
     * Tests that setters and getters are working properly
     */
    public function testSettersAndGetters()
    {
        // Create the objects that will be set
        $testMessageId = 'testMessageId';
        $collaboration = new Collaboration();
        $messageCollection = new Collection();
        $listener = new StartEvent();

        // Use the setters of the collaboration
        $collaboration->setClosed(false);
        $collaboration->setMessageFlows($messageCollection);

        //subscribe and unsubscribe a listener
        $collaboration->subscribe($listener, $testMessageId);
        $collaboration->unsubscribe($listener, $testMessageId);

        //Assertion: The properties must be accessible with the getters
        $this->assertEquals(0, $collaboration->getCorrelationKeys()->count());
        $this->assertEquals(0, $collaboration->getMessageFlows()->count());
        $this->assertFalse($collaboration->isClosed());
    }
}