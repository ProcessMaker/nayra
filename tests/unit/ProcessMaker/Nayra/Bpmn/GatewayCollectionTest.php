<?php

namespace ProcessMaker\Nayra\Bpmn;

use PHPUnit\Framework\TestCase;
use ProcessMaker\Nayra\Bpmn\Models\GatewayCollection;
use ProcessMaker\Nayra\Bpmn\Models\InclusiveGateway;

/**
 * Tests for the gateway collection
 */
class GatewayCollectionTest extends TestCase
{
    /**
     * Test the adding of items to the collection
     */
    public function testAdd()
    {
        // create and element
        $element = new InclusiveGateway();
        $collection = new GatewayCollection();
        // add the element to the collection
        $collection->add($element);

        //Assertion: the first element of the collection should be the added element
        $this->assertEquals($element, $collection->item(0));
    }
}
