<?php

namespace ProcessMaker\Nayra\Bpmn;

use PHPUnit\Framework\TestCase;
use ProcessMaker\Nayra\Bpmn\Models\Flow;
use ProcessMaker\Nayra\Bpmn\Models\FlowCollection;

/**
 * Tests for the flow collection
 */
class FlowCollectionTest extends TestCase
{
    /**
     * Test the adding of items to the collection
     */
    public function testAdd()
    {
        // create and element
        $element = new Flow();
        $collection = new FlowCollection();
        // add the element to the collection
        $collection->add($element);

        //Assertion: the first element of the collection should be the added element
        $this->assertEquals($element, $collection->item(0));
    }
}
