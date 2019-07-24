<?php

namespace ProcessMaker\Nayra\Bpmn;

use PHPUnit\Framework\TestCase;
use ProcessMaker\Nayra\Bpmn\Models\BoundaryEvent;

class BoundaryEventTest extends TestCase
{
    /**
     * Tests that the input place of a boundary event must be null
     */
    public function testGetInputPlaceOfNewElement()
    {
        $boundaryEvent = new BoundaryEvent();

        //the input place should be null in a start event
        $this->assertNull($boundaryEvent->getInputPlace());
    }
}
