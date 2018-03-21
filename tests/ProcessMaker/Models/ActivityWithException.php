<?php

namespace ProcessMaker\Models;

use ProcessMaker\Nayra\Bpmn\ActivityTrait;
use ProcessMaker\Nayra\Contracts\Bpmn\ActivityInterface;
use ProcessMaker\Nayra\Exceptions\ActivityWorkException;

/**
 * This activity will raise an exception when executed.
 *
 */
class ActivityWithException implements ActivityInterface
{
    use ActivityTrait,
        LocalFlowNodeTrait,
        LocalProcessTrait,
        LocalPropertiesTrait;

    /**
     * Called when activated.
     *
     * @throws ActivityWorkException
     */
    public function work()
    {
        throw new ActivityWorkException();
    }

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
