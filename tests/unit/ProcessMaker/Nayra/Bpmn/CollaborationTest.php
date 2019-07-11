<?php

namespace ProcessMaker\Nayra\Bpmn;

use PHPUnit\Framework\TestCase;
use ProcessMaker\Nayra\Bpmn\Models\Collaboration;
use ProcessMaker\Nayra\Bpmn\Models\StartEvent;

/**
 * Tests for the Collaboration class
 *
 * @package ProcessMaker\Nayra\Bpmn
 */
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

        //Assertion: The properties must be accessible with the getters
        $this->assertEquals(0, $collaboration->getCorrelationKeys()->count());
        $this->assertEquals(0, $collaboration->getMessageFlows()->count());
        $this->assertFalse($collaboration->isClosed());
    }
}
