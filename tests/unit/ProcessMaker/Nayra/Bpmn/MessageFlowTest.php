<?php

namespace ProcessMaker\Nayra\Bpmn;

use PHPUnit\Framework\TestCase;
use ProcessMaker\Nayra\Bpmn\Models\Collaboration;
use ProcessMaker\Nayra\Bpmn\Models\EndEvent;
use ProcessMaker\Nayra\Bpmn\Models\IntermediateCatchEvent;
use ProcessMaker\Nayra\Bpmn\Models\Message;
use ProcessMaker\Nayra\Bpmn\Models\MessageEventDefinition;
use ProcessMaker\Nayra\Bpmn\Models\MessageFlow;

/**
 * Tests for the MessageFlow class
 *
 * @package ProcessMaker\Nayra\Bpmn
 */
class MessageFlowTest extends TestCase
{
    /**
     * Tests that setters and getters are working properly
     */
    public function testSettersAndGetters()
    {
        // Create the objects that will be set in the data store
        $msgFlow = new MessageFlow();
        $collaboration = new Collaboration();
        $target = new IntermediateCatchEvent();
        $source = new EndEvent();
        $message = new Message();
        $messageEventDef = new MessageEventDefinition();
        $messageEventDef->setPayload($message);

        $source->getEventDefinitions()->push($messageEventDef);

        // Use the setters
        $msgFlow->setCollaboration($collaboration);
        $msgFlow->setSource($source);
        $msgFlow->setTarget($target);
        $msgFlow->setMessage($message);

        //Assertion: The get message must be equal to the set one
        $this->assertEquals($message, $msgFlow->getMessage());

        //Assertion: The get target must be equal to the set one
        $this->assertEquals($target, $msgFlow->getTarget());

        //Assertion: The get collaboration must be equal to the set one
        $this->assertEquals($collaboration, $msgFlow->getCollaboration());
    }
}
