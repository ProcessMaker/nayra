<?php

namespace ProcessMaker\Nayra\Bpmn\Events;

use ProcessMaker\Nayra\Contracts\Bpmn\ActivityInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\TokenInterface;

/**
 * Event raised when and activity is activated.
 *
 * @package ProcessMaker\Models
 */
class ActivityCompletedEvent
{
    /**
     * @var ActivityInterface
     */
    public $activity;

    /**
     * @var \ProcessMaker\Nayra\Contracts\Bpmn\TokenInterface
     */
    public $token;

    /**
     * ActivityCompletedEvent constructor.
     *
     * @param ActivityInterface $activity
     * @param TokenInterface $token
     */
    public function __construct(ActivityInterface $activity, TokenInterface $token)
    {
        $this->activity = $activity;
        $this->token = $token;
    }
}
