<?php

namespace ProcessMaker\Nayra\Bpmn;


use PHPUnit\Framework\TestCase;
use ProcessMaker\Nayra\Bpmn\Models\Message;
use ProcessMaker\Nayra\Bpmn\Models\Operation;
use ProcessMaker\Nayra\Contracts\Bpmn\OperationInterface;

class OperationTest extends TestCase
{
    /**
     * Tests that setters and getters are working properly
     */
    public function testSettersAndGetters()
    {
        // Create the objects that will be set in the data store
        $operation = new Operation();


        $dummyFunction = function() {return true;};
        $dummyMessage = new Message();
        // Use the setters
        $operation->setProperty(OperationInterface::BPMN_PROPERTY_IMPLEMENTATION, $dummyFunction);
        $operation->setProperty(OperationInterface::BPMN_PROPERTY_IN_MESSAGE, $dummyMessage);
        $operation->setProperty(OperationInterface::BPMN_PROPERTY_OUT_MESSAGE, $dummyMessage);
        $operation->setProperty(OperationInterface::BPMN_PROPERTY_ERRORS, []);

        //Assertion: The getters should return the set objects
        $this->assertEquals($dummyFunction, $operation->getImplementation());
        $this->assertEquals($dummyMessage, $operation->getInMessage());
        $this->assertEquals($dummyMessage, $operation->getOutMessage());
        $this->assertEquals([], $operation->getErrors());
    }
}