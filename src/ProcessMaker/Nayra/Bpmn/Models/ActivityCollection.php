<?php

namespace ProcessMaker\Nayra\Bpmn\Models;

use ProcessMaker\Nayra\Bpmn\Collection;
use ProcessMaker\Nayra\Contracts\Bpmn\ActivityCollectionInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\ActivityInterface;

/**
 * ActivityCollection
 *
 * @package ProcessMaker\Models
 */
class ActivityCollection extends Collection implements ActivityCollectionInterface
{

    /**
     * Add an activity to the collection.
     *
     * @param ActivityInterface $element
     *
     * @return $this
     */
    public function add(ActivityInterface $element)
    {
        $this->push($element);
        return $this;
    }
}
