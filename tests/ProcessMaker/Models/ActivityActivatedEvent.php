<?php

namespace ProcessMaker\Models;

use ProcessMaker\Nayra\Contracts\Bpmn\ActivityInterface;

/**
 * Event raised when and activity is activated.
 *
 * @package ProcessMaker\Models
 */
class ActivityActivatedEvent
{
    /**
     * ActivityActivatedEvent constructor.
     *
     * @param ActivityInterface $activity
     * @param array $params
     */
    public function __construct(ActivityInterface $activity, $params = [])
    {
    }
}
