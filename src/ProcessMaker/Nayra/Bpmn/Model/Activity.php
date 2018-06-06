<?php

namespace ProcessMaker\Nayra\Bpmn\Model;

use ProcessMaker\Nayra\Bpmn\ActivityTrait;
use ProcessMaker\Nayra\Contracts\Bpmn\ActivityInterface;

/**
 * Activity implementation.
 *
 * @package ProcessMaker\Models
 */
class Activity implements ActivityInterface
{
    use ActivityTrait;

    /**
     * Array map of custom event classes for the bpmn element.
     *
     * @return array
     */
    protected function getBpmnEventClasses()
    {
        return [
            ActivityInterface::EVENT_ACTIVITY_ACTIVATED => ActivityActivatedEvent::class,
        ];
    }
}
