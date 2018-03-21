<?php

namespace ProcessMaker\Nayra\Contracts\Bpmn;

/**
 * Collection of processes.
 *
 * @package ProcessMaker\Nayra\Contracts\Bpmn
 */
interface ProcessCollectionInterface extends CollectionInterface
{

    /**
     * Add an element to the collection.
     *
     * @param ProcessInterface $element
     *
     * @return $this
     */
    public function add(ProcessInterface $element);
}
