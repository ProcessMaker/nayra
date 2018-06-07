<?php

namespace ProcessMaker\Nayra\Bpmn;

use PHPUnit\Framework\TestCase;
use ProcessMaker\Nayra\Bpmn\Model\Activity;
use ProcessMaker\Nayra\Bpmn\Model\ActivityCollection;

/**
 * Tests the activity collection
 *
 * @package ProcessMaker\Nayra\Bpmn
 */
class ActivityCollectionTest extends TestCase
{
    /**
     * Test the adding of items to the collection
     */
    public function testAdd()
    {
        // create and element
        $element = new Activity();
        $collection = new ActivityCollection();
        // add the element to the collection
        $collection->add($element);

        //Assertion: the first element of the collection should be the added element
        $this->assertEquals($element, $collection->item(0));
    }
}
