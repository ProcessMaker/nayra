<?php

namespace ProcessMaker\Nayra\Contracts\Bpmn;

use SeekableIterator;

/**
 * CollectionInterface
 *
 * @package ProcessMaker\Nayra\Contracts\Bpmn
 */
interface CollectionInterface extends SeekableIterator
{

    /**
     * Count the elements of the collection.
     *
     * @return integer
     */
    public function count();

    /**
     * Find elements of the collection that match the $condition.
     *
     * @param callback $condition
     *
     * @return CollectionInterface Filtered collection
     */
    public function find($condition);

    /**
     * Add an element to the collection.
     *
     * @param mixed $item
     */
    public function push($item);

    /**
     * Pop an element from the collection.
     *
     * @return mixed
     */
    public function pop();

    /**
     * Unshift element.
     *
     * @param type $item
     *
     * @return mixed Unshift element
     */
    public function unshift($item);

    /**
     * Get the index of the element in the collection.
     *
     * @param type $item
     *
     * @return integer
     */
    public function indexOf($item);

    /**
     * Sum the $callback result for each element.
     *
     * @param callable $callback
     *
     * @return double
     */
    public function sum(callable $callback);

    /**
     * Get a item by index
     *
     * @return mixed
     */
    public function item($index);

    /**
     * Remove a portion of the collection and replace it with something else.
     *
     * @param $offset
     * @param null $length
     * @param null $replacement
     * @return array
     */
    public function splice($offset, $length = null, $replacement = null);

    /**
     * Converts the collection to an array
     *
     * @return void
     */
    public function toArray();
}
