<?php

namespace ProcessMaker\Nayra\Bpmn\Model;


use ProcessMaker\Nayra\Bpmn\Collection;
use ProcessMaker\Nayra\Contracts\Bpmn\FlowCollectionInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\FlowInterface;

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
