<?php

namespace ProcessMaker\Nayra\Bpmn;

use PHPUnit\Framework\TestCase;
use ProcessMaker\Nayra\Bpmn\Model\EventCollection;
use ProcessMaker\Nayra\Bpmn\Model\StartEvent;

/**
 * Tests for the event collection
 *
 * @package ProcessMaker\Nayra\Bpmn
 */
class EventCollectionTest extends TestCase
{
    /**
     * Test the adding of items to the collection
     */
    public function testAdd()
    {
        // create and element
        $element = new StartEvent();
        $collection = new EventCollection();
        // add the element to the collection
        $collection->add($element);

        //Assertion: the first element of the collection should be the added element
        $this->assertEquals($element, $collection->item(0));
    }
}
