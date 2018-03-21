<?php

namespace ProcessMaker\Nayra\Bpmn;

use PHPUnit\Framework\TestCase;

class CollectionTest extends TestCase
{
    /**
     * @var Collection $object
     */
    private $object;

    public function setUp()
    {
        parent::setUp();
        $this->object = new Collection([1, 2, 3]);
    }

    /**
     * Test count.
     *
     */
    public function testCount()
    {
        $this->assertEquals(3, $this->object->count());
    }

    /**
     * Find elements of the collection that match the $condition.
     *
     */
    public function testFind()
    {
        $this->assertEquals(1, $this->object->find(function ($item) {
            return $item === 2;
        })->count());
        $this->assertEquals(2, $this->object->find(function ($item) {
            return $item % 2;
        })->count());
    }

    /**
     * Test push item.
     *
     */
    public function testPush()
    {
        $this->object->push(4);
        $this->assertEquals(4, $this->object->count());
    }

    /**
     * Test pop item.
     *
     */
    public function testPop()
    {
        $this->assertEquals(3, $this->object->pop());
        $this->assertEquals(2, $this->object->count());
    }

    /**
     * Test unshift.
     *
     */
    public function testUnshift()
    {
        $this->object->unshift(0);
        $this->assertEquals(0, $this->object->item(0));
        $this->assertEquals(4, $this->object->count());
    }

    /**
     * Test indexOf.
     *
     */
    public function testIndexOf()
    {
        $this->assertEquals(1, $this->object->indexOf(2));
    }

    /**
     * Sum the $callback result for each element.
     *
     */
    public function testSum()
    {
        $this->assertEquals(6, $this->object->sum(function($item){return $item;}));
    }

    /**
     * Test get item.
     *
     */
    public function testItem()
    {
        $this->assertEquals(2, $this->object->item(1));
    }

    /**
     * Test splice.
     *
     */
    public function testSplice()
    {
        $this->object->splice(1,1, [20]);
        $this->assertEquals(20, $this->object->item(1));
        $this->object->splice(1,1, [20]);
        $this->assertEquals(20, $this->object->item(1));
        $this->object->splice(1,1, [20, 21]);
        $this->assertEquals(20, $this->object->item(1));
        $this->assertEquals(21, $this->object->item(2));
        $this->assertEquals(3, $this->object->item(3));
    }

    /**
     * Test current
     *
     */
    public function testCurrent()
    {
        foreach($this->object as $item) {
            $this->assertEquals($item, $this->object->current());
        }
    }

    /**
     * Test next
     *
     */
    public function testNext()
    {
        $this->assertEquals(1, $this->object->current());
        $this->object->next();
        $this->assertEquals(2, $this->object->current());
    }

    /**
     * Test get key.
     *
     */
    public function testKey()
    {
        $this->assertEquals(0, $this->object->key());
        $this->object->next();
        $this->assertEquals(1, $this->object->key());
        $this->assertEquals(2, $this->object->current());
    }

    /**
     * Test valid.
     *
     */
    public function testValid()
    {
        $this->assertTrue($this->object->valid());
        $this->object->next();
        $this->object->next();
        $this->object->next();
        $this->assertFalse($this->object->valid());
    }

    /**
     * Test rewind
     *
     */
    public function testRewind()
    {
        $this->object->next();
        $this->object->next();
        $this->object->rewind();
        $this->assertEquals(0, $this->object->key());
    }

    /**
     * Test seek
     *
     */
    public function testSeek()
    {
        $this->object->seek(1);
        $this->assertEquals(1, $this->object->key());
        $this->assertEquals(2, $this->object->current());
    }
}
