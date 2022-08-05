<?php

namespace ProcessMaker\Nayra\Contracts\Bpmn;

/**
 * Collection of shapes.
 */
interface ShapeCollectionInterface extends CollectionInterface
{
    /**
     * Add an element to the collection.
     *
     * @param ShapeInterface $element
     *
     * @return $this
     */
    public function add(ShapeInterface $element);
}
