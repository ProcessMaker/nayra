<?php

namespace ProcessMaker\Models;

use ProcessMaker\Nayra\Bpmn\EndEventTrait;

/**
 * End event implementation.
 *
 * @package ProcessMaker\Models
 */
class EndEvent implements \ProcessMaker\Nayra\Contracts\Bpmn\EventInterface
{

    use EndEventTrait,
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
