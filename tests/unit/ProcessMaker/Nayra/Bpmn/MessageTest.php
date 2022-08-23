<?php

namespace ProcessMaker\Nayra\Bpmn;

use PHPUnit\Framework\TestCase;
use ProcessMaker\Nayra\Bpmn\Models\Message;

/**
 * Tests for the Message class
 */
class MessageTest extends TestCase
{
    /**
     * Tests that setters and getters are working properly
     */
    public function testSettersAndGetters()
    {
        // Create the objects that will be set in the data store
        $message = new Message();
        $testString = 'testString';

        //set process and state object to the data store
        $message->setId($testString);

        //Assertion: The get id must be equal to the set one
        $this->assertEquals($testString, $message->getId());

        //Assertion: The name was not set so it must be null
        $this->assertNull($message->getName());
    }
}
