<?php

namespace ProcessMaker\Nayra\Contracts\Bpmn;

/**
 * Collection of Events.
 *
 * @package ProcessMaker\Nayra\Contracts\Bpmn
 */
interface EventCollectionInterface extends CollectionInterface
{

    /**
     * Add an element to the collection.
     *
     * @param EventNodeInterface $element
     *
     * @return $this
     */
    public function add(EventNodeInterface $element);

    /**
     * Get a item by index
     *
     * @param $index
     *
     * @return EventNodeInterface
     */
    public function item($index);
}
