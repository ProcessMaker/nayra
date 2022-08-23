<?php

namespace ProcessMaker\Nayra\Bpmn;

use ProcessMaker\Nayra\Contracts\Bpmn\CollectionInterface;

/**
 * Collection of elements
 */
class Collection implements CollectionInterface
{
    /**
     * Items of the collection.
     *
     * @var array
     */
    private $items = [];

    /**
     * Index of the iterator.
     *
     * @var int
     */
    private $index = 0;

    /**
     * Collection constructor.
     *
     * @param array $items
     */
    public function __construct($items = [])
    {
        $this->items = $items;
    }

    /**
     * Count the elements of the collection.
     *
     * @return int
     */
    public function count()
    {
        return count($this->items);
    }

    /**
     * Find elements of the collection that match the $condition.
     *
     * @param callable $condition
     *
     * @return CollectionInterface Filtered collection
     */
    public function find($condition)
    {
        return new self(array_values(array_filter($this->items, $condition)));
    }

    /**
     * Find first element of the collection that match the $condition.
     *
     * @param callable $condition
     *
     * @return mixed
     */
    public function findFirst(callable $condition)
    {
        return current(array_filter($this->items, $condition));
    }

    /**
     * Add an element to the collection.
     *
     * @param mixed $item
     */
    public function push($item)
    {
        $this->items[] = $item;
    }

    /**
     * Pop an element from the collection.
     *
     * @return mixed
     */
    public function pop()
    {
        return array_pop($this->items);
    }

    /**
     * Unshift element.
     *
     * @param mixed $item
     *
     * @return mixed Unshift element
     */
    public function unshift($item)
    {
        return array_unshift($this->items, $item);
    }

    /**
     * Get the index of the element in the collection.
     *
     * @param mixed $item
     *
     * @return int
     */
    public function indexOf($item)
    {
        return array_search($item, $this->items, true);
    }

    /**
     * Sum the $callback result for each element.
     *
     * @param callable $callback
     *
     * @return float
     */
    public function sum(callable $callback)
    {
        return array_reduce(
            $this->items,
            function ($carry, $item) use ($callback) {
                return $carry + $callback($item);
            }
        );
    }

    /**
     * Get a item by index
     *
     * @param int $index
     *
     * @return mixed
     */
    public function item($index)
    {
        return $this->items[$index];
    }

    /**
     * Remove a portion of the collection and replace it with something else.
     *
     * @param int $offset
     * @param mixed $length
     * @param mixed $replacement
     * @return array
     */
    public function splice($offset, $length = null, $replacement = null)
    {
        return array_splice($this->items, $offset, $length, $replacement);
    }

    /**
     * Return the current element
     *
     * @return mixed Can return any type.
     */
    public function current()
    {
        return $this->item($this->index);
    }

    /**
     * Move forward to next element
     *
     * @return void Any returned value is ignored.
     */
    public function next()
    {
        $this->index++;
    }

    /**
     * Return the key of the current element
     *
     * @return mixed scalar on success, or null on failure.
     */
    public function key()
    {
        return $this->index;
    }

    /**
     * Checks if current position is valid
     *
     * @return bool The return value will be casted to boolean and then evaluated.
     * Returns true on success or false on failure.
     */
    public function valid()
    {
        return isset($this->items[$this->index]);
    }

    /**
     * Rewind the Iterator to the first element
     *
     * @return void Any returned value is ignored.
     */
    public function rewind()
    {
        $this->index = 0;
    }

    /**
     * Seeks to a position
     *
     * @param int $index The position to seek to.
     *
     * @return void
     */
    public function seek($index)
    {
        $this->index = $index;
    }

    /**
     * Converts the collection to an array
     *
     * @return array
     */
    public function toArray()
    {
        return $this->items;
    }
}
