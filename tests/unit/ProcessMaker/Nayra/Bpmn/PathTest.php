<?php

namespace ProcessMaker\Nayra\Bpmn;

use PHPUnit\Framework\TestCase;
use ProcessMaker\Nayra\Bpmn\Path;

class PathTest extends TestCase
{
    /**
     * Tests that a path stores and returns correctly its elements
     */
    public function testGetElement()
    {
        //Create a new empty path
        $path = new Path([]);

        //Assertion: as the path is empty, the number of elements should be 0
        $this->assertCount(0, $path->getElements());

        //Create a new path with one element
        $path = new Path([1]);

        //Assertion: the created path should have one element
        $this->assertCount(1, $path->getElements());
    }
}
