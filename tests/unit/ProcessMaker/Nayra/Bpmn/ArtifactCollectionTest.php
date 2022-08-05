<?php

namespace ProcessMaker\Nayra\Bpmn;

use PHPUnit\Framework\TestCase;
use ProcessMaker\Nayra\Bpmn\Models\Artifact;
use ProcessMaker\Nayra\Bpmn\Models\ArtifactCollection;

/**
 * Tests for the artifact collection
 */
class ArtifactCollectionTest extends TestCase
{
    /**
     * Test the adding of items to the collection
     */
    public function testAdd()
    {
        // create and element
        $element = new Artifact();
        $collection = new ArtifactCollection();

        // add the element to the collection
        $collection->add($element);

        //Assertion: the first element of the collection should be the added element
        $this->assertEquals($element, $collection->item(0));
    }
}
