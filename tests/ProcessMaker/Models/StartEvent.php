<?php

namespace ProcessMaker\Models;

use ProcessMaker\Nayra\Bpmn\StartEventTrait;
use ProcessMaker\Nayra\Contracts\Bpmn\StartEventInterface;

/**
 * Start Event implementation.
 *
 * @package ProcessMaker\Models
 */
class StartEvent implements StartEventInterface
{

    use StartEventTrait,
        LocalFlowNodeTrait,
        LocalProcessTrait,
        LocalPropertiesTrait;

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
