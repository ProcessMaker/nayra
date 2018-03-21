<?php

namespace ProcessMaker\Models;

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

    public function add(ActivityInterface $element)
    {
        $this->push($element);
        return $this;
    }
}
