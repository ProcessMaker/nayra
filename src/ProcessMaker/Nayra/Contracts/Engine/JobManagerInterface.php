<?php

namespace ProcessMaker\Nayra\Contracts\Engine;

use ProcessMaker\Nayra\Contracts\Bpmn\CatchEventInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\ConditionalEventDefinitionInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\EntityInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\FlowElementInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\TimerEventDefinitionInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\TokenInterface;

/**
 * Job manager required for scheduling timer events.
 *
 * This interface also defines the different types of schedules that a timer
 * event could use.
 */
interface JobManagerInterface
{
    const EVENT_SCHEDULE_DATE = 'ScheduleDate';
    const EVENT_SCHEDULE_CYCLE = 'ScheduleCycle';
    const EVENT_SCHEDULE_DURATION = 'ScheduleDuration';

    /**
     * Schedule a job for a specific date and time for the given BPMN element,
     * event definition and an optional Token object
     *
     * @param string $datetime in ISO-8601 format
     * @param TimerEventDefinitionInterface $eventDefinition
     * @param EntityInterface $element
     * @param TokenInterface $token
     *
     * @return $this
     */
    public function scheduleDate($datetime, TimerEventDefinitionInterface $eventDefinition,
                                 FlowElementInterface $element, TokenInterface $token = null);

    /**
     * Schedule a job for a specific cycle for the given BPMN element, event definition
     * and an optional Token object
     *
     * @param string $cycle in ISO-8601 format
     * @param TimerEventDefinitionInterface $eventDefinition
     * @param EntityInterface $element
     * @param TokenInterface $token
     */
    public function scheduleCycle($cycle, TimerEventDefinitionInterface $eventDefinition, FlowElementInterface $element,
                                  TokenInterface $token = null);

    /**
     * Schedule a job execution after a time duration for the given BPMN element,
     * event definition and an optional Token object
     *
     * @param string $duration in ISO-8601 format
     * @param TimerEventDefinitionInterface $eventDefinition
     * @param EntityInterface $element
     * @param TokenInterface $token
     */
    public function scheduleDuration($duration, TimerEventDefinitionInterface $eventDefinition,
                                     FlowElementInterface $element, TokenInterface $token = null);
}
