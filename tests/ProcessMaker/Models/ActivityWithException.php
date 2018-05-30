<?php

namespace ProcessMaker\Models;

use ProcessMaker\Nayra\Bpmn\ActivityTrait;
use ProcessMaker\Nayra\Contracts\Bpmn\ActivityInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\TokenInterface;

/**
 * This activity will raise an exception when executed.
 *
 */
class ActivityWithException implements ActivityInterface
{
    use ActivityTrait;

    /**
     * Configure the activity to go to a FAILING status when activated.
     *
     */
    protected function initActivity()
    {
        $this->attachEvent(ActivityInterface::EVENT_ACTIVITY_ACTIVATED, function ($self, TokenInterface $token) {
            $token->setStatus(ActivityInterface::TOKEN_STATE_FAILING);
        });
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
