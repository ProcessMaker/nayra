<?php

namespace ProcessMaker\Nayra\Bpmn;

use PHPUnit\Framework\TestCase;
use ProcessMaker\Models\StartEvent;

class StartEventTest extends TestCase
{
    /**
     * Tests that the input place of a start event must be null
     */
    public function testGetInputPlaceOfNewElement()
    {
        $start = new StartEvent();

        //the input place should be null in a start event
        $this->assertNull($start->getInputPlace());
    }
}
