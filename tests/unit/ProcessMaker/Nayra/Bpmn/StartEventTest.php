<?php

namespace ProcessMaker\Nayra\Bpmn;

use PHPUnit\Framework\TestCase;
use ProcessMaker\Nayra\Bpmn\Models\StartEvent;

/**
 * StartEvent unit test.
 */
class StartEventTest extends TestCase
{
    /**
     * Tests that the input place of a start event must be null
     */
    public function testGetInputPlaceOfNewElement()
    {
        $start = new StartEvent();

        // Assertion: the input place should be null in a start event
        $this->assertNull($start->getInputPlace());
        // Assertion: the active state is null
        $this->assertNull($start->getActiveState());
    }
}
