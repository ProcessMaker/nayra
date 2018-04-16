<?php

namespace ProcessMaker\Models;

use ProcessMaker\Nayra\Bpmn\ParticipantTrait;
use ProcessMaker\Nayra\Contracts\Bpmn\ParticipantInterface;

/**
 * Activity implementation.
 *
 * @package ProcessMaker\Models
 */
class Participant implements ParticipantInterface
{
    use ParticipantTrait,
        LocalFlowNodeTrait,
        //LocalProcessTrait,
        LocalPropertiesTrait;


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
