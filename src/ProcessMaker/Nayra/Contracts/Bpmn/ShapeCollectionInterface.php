<?php

namespace ProcessMaker\Nayra\Contracts\Bpmn;

/**
 * Collection of shapes.
 *
 * @package ProcessMaker\Nayra\Contracts\Bpmn
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
