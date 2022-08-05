<?php

namespace ProcessMaker\Nayra\Bpmn\Models;

use ProcessMaker\Nayra\Bpmn\Collection;
use ProcessMaker\Nayra\Contracts\Bpmn\FlowCollectionInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\FlowInterface;

/**
 * Collection of flows.
 */
class FlowCollection extends Collection implements FlowCollectionInterface
{
    /**
     * Add an element to the collection.
     *
     * @param FlowInterface $element
     *
     * @return $this
     */
    public function add(FlowInterface $element)
    {
        $this->push($element);

        return $this;
    }
}
