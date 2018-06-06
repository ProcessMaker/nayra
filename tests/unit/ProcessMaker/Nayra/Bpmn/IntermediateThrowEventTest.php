<?php

namespace ProcessMaker\Nayra\Bpmn;


use PHPUnit\Framework\TestCase;
use ProcessMaker\Nayra\Bpmn\Models\IntermediateThrowEvent;

class IntermediateThrowEventTest extends TestCase
{
    /**
     * Tests that setters and getters are working properly
     */
    public function testSettersAndGetters()
    {
        // Create the objects that will be set in the data store
        $event = new IntermediateThrowEvent();

        //Assertion: The collections getters must be not null
        $this->assertEquals(0, $event->getDataInputAssociations()->count());
        $this->assertEquals(0, $event->getDataInputs()->count());

        //Assertion: input set was not initialized so it must benull
        $this->assertNull($event->getInputSet());
    }
}