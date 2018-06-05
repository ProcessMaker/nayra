<?php

namespace ProcessMaker\Nayra\Bpmn\Models;

use ProcessMaker\Nayra\Bpmn\IntermediateCatchEventTrait;
use ProcessMaker\Nayra\Contracts\Bpmn\IntermediateCatchEventInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\MessageListenerInterface;

/**
 * IntermediateThrowEvent implementation.
 *
 * @package ProcessMaker\Models
 */
class IntermediateCatchEvent implements IntermediateCatchEventInterface, MessageListenerInterface
{

    use IntermediateCatchEventTrait;

   protected function getBpmnEventClasses()
    {
        return [];
    }
}
