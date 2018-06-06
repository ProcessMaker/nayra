<?php

namespace ProcessMaker\Nayra\Bpmn\Model;

use ProcessMaker\Nayra\Bpmn\Collection;
use ProcessMaker\Nayra\Contracts\Bpmn\EventCollectionInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\EventInterface;

/**
 * Event Collection
 *
 * @package ProcessMaker\Models
 */
class EventCollection extends Collection implements EventCollectionInterface
{
    /**
     * Add an element to the collection.
     *
     * @param EventInterface $element
     *
     * @return $this
     */
    public function add(EventInterface $element)
    {
        $this->push($element);
        return $this;
    }
}
