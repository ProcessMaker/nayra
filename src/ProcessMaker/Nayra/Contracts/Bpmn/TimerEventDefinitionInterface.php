<?php

namespace ProcessMaker\Nayra\Contracts\Bpmn;

/**
 * TimerEventDefinition interface.
 *
 * @package ProcessMaker\Nayra\Contracts\Bpmn
 */
interface TimerEventDefinitionInterface extends EventDefinitionInterface
{
    const BPMN_PROPERTY_TIME_DATE = 'timeDate';
    const BPMN_PROPERTY_TIME_CYCLE = 'timeCycle';
    const BPMN_PROPERTY_TIME_DURATION = 'timeDuration';

    const EVENT_THROW_EVENT_DEFINITION = 'ThrowTimerEvent';
    const EVENT_CATCH_EVENT_DEFINITION = 'CatchTimerEvent';

    /**
     * Get the date expression for the timer event definition.
     *
     * @return \ProcessMaker\Nayra\Contracts\Bpmn\FormalExpressionInterface
     */
    public function getTimeDate();

    /**
     * Get the cycle expression for the timer event definition.
     *
     * @return \ProcessMaker\Nayra\Contracts\Bpmn\FormalExpressionInterface
     */
    public function getTimeCycle();

    /**
     * Get the duration expression for the timer event definition.
     *
     * @return \ProcessMaker\Nayra\Contracts\Bpmn\FormalExpressionInterface
     */
    public function getTimeDuration();
}
