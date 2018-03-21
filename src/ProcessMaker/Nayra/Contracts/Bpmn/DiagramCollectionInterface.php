<?php

namespace ProcessMaker\Nayra\Contracts\Bpmn;

/**
 * Collection of process diagrams.
 *
 * @package ProcessMaker\Nayra\Contracts\Bpmn
 */
interface DiagramCollectionInterface extends CollectionInterface
{

    /**
     * Add an element to the collection.
     *
     * @param DiagramInterface $element
     *
     * @return $this
     */
    public function add(DiagramInterface $element);
}
