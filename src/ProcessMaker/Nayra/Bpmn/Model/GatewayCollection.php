<?php

namespace ProcessMaker\Nayra\Bpmn\Model;


use ProcessMaker\Nayra\Contracts\Bpmn\GatewayCollectionInterface;
use ProcessMaker\Nayra\Bpmn\Collection;
use ProcessMaker\Nayra\Contracts\Bpmn\GatewayInterface;

/**
 * Gateway Collection
 *
 * @package ProcessMaker\Models
 */
class GatewayCollection extends Collection implements GatewayCollectionInterface
{

    /**
     * Add an element to the collection.
     *
     * @param GatewayInterface $element
     *
     * @return $this
     */
    public function add(GatewayInterface $element)
    {
        $this->push($element);
        return $this;
    }
}