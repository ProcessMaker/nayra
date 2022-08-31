<?php

namespace ProcessMaker\Nayra\Bpmn;

use PHPUnit\Framework\TestCase;
use ProcessMaker\Nayra\Bpmn\Models\Error;
use ProcessMaker\Nayra\Bpmn\Models\MessageFlow;
use ProcessMaker\Nayra\Contracts\Bpmn\ErrorInterface;

/**
 * Tests for the Error class
 */
class ErrorTest extends TestCase
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
        $error->setProperty(ErrorInterface::BPMN_PROPERTY_NAME, $testString);
        $error->setProperty(ErrorInterface::BPMN_PROPERTY_ERROR_CODE, $testString);

        //Assertion: The get name must be equal to the set one
        $this->assertEquals($testString, $error->getName());

        //Assertion: The get error code must be equal to the set one
        $this->assertEquals($testString, $error->getErrorCode());
    }
}
