<?php

namespace ProcessMaker\Nayra\Bpmn;


use PHPUnit\Framework\TestCase;

class MessageTest extends TestCase
{
    /**
     * Tests that setters and getters are working properly
     */
    public function testSettersAndGetters()
    {
        // Create the objects that will be set in the data store
        $error = new Error();
        $message = new MessageFlow();
        $testString = 'oneString';

        //set process and state object to the data store
        $error->setMessageFlow($message);
        $error->setProperty(ErrorInterface::BPMN_PROPERTY_NAME, $testString);
        $error->setProperty(ErrorInterface::BPMN_PROPERTY_ERROR_CODE, $testString);


        //Assertion: The set name must be equal to the created one
        $this->assertEquals($testString, $error->getName());

        //Assertion: The set error code must be equal to the created one
        $this->assertEquals($testString, $error->getErrorCode());
    }
}