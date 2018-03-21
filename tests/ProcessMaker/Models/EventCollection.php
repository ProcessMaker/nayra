<?php

namespace ProcessMaker\Models;

use ProcessMaker\Nayra\Bpmn\Collection;
use ProcessMaker\Nayra\Contracts\Bpmn\EventCollectionInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\EventNodeInterface;

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
     * @param EventNodeInterface $element
     *
     * @return $this
     */
    public function add(EventNodeInterface $element)
    {
        $this->push($element);
        return $this;
    }
}
