<?php

namespace ProcessMaker\Nayra\Bpmn\Models;

use ProcessMaker\Nayra\Bpmn\BoundaryEventTrait;
use ProcessMaker\Nayra\Contracts\Bpmn\BoundaryEventInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\MessageListenerInterface;

/**
 * BoundaryEvent implementation.
 */
class BoundaryEvent implements BoundaryEventInterface, MessageListenerInterface
{
    use BoundaryEventTrait;

    /**
     * Get BPMN event classes.
     *
     * @return array
     */
    protected function getBpmnEventClasses()
    {
        return [];
    }
}
