<?php

namespace ProcessMaker\Nayra\Model;

use ProcessMaker\Nayra\Bpmn\StartEventTrait;
use ProcessMaker\Nayra\Contracts\Bpmn\StartEventInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\MessageListenerInterface;

/**
 * Class StartEvent
 * @package ProcessMaker\Nayra\Model
 */
class StartEvent implements StartEventInterface, MessageListenerInterface
{

    use StartEventTrait;

    /**
     * Array map of custom event classes for the bpmn element.
     *
     * @return array
     */
    protected function getBpmnEventClasses()
    {
        return [];
    }
}
