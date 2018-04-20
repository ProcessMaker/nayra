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
     * @param EventInterface $element
     *
     * @return $this
     */
    public function add(EventInterface $element);

    /**
     * Get a item by index
     *
     * @param $index
     *
     * @return EventInterface
     */
    public function item($index);
}
