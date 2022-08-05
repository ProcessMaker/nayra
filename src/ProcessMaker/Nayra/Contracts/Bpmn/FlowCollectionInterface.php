<?php

namespace ProcessMaker\Nayra\Contracts\Bpmn;

/**
 * Collection of flows.
 */
interface FlowCollectionInterface extends CollectionInterface
{
    /**
     * Add an element to the collection.
     *
     * @param FlowInterface $element
     *
     * @return $this
     */
    public function add(FlowInterface $element);
}
