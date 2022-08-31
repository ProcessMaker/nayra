<?php

namespace ProcessMaker\Nayra\Contracts\Bpmn;

use SeekableIterator;

/**
 * CollectionInterface
 */
interface CollectionInterface extends SeekableIterator
{
    /**
     * Count the elements of the collection.
     *
     * @return int
     */
    public function count();

    /**
     * Find elements of the collection that match the $condition.
     *
     * @param callable $condition
     *
     * @return CollectionInterface Filtered collection
     */
    public function find($condition);

    /**
     * Find first element of the collection that match the $condition.
     *
     * @param callable $condition
     *
     * @return mixed
     */
    public function findFirst(callable $condition);

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
     * @param mixed $item
     *
     * @return mixed Unshift element
     */
    public function unshift($item);

    /**
     * Get the index of the element in the collection.
     *
     * @param mixed $item
     *
     * @return int
     */
    public function indexOf($item);

    /**
     * Sum the $callback result for each element.
     *
     * @param callable $callback
     *
     * @return float
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
     * @return array
     */
    public function toArray();
}
