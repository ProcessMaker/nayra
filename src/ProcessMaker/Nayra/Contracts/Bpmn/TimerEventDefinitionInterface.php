<?php

namespace ProcessMaker\Nayra\Contracts\Bpmn;

use ProcessMaker\Nayra\Contracts\Engine\EngineInterface;

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

    /**
     * Set the date expression for the timer event definition.
     *
     * @param callable $timeExpression
     */
    public function setTimeDate(callable $timeExpression);

    /**
     * Set the cycle expression for the timer event definition.
     *
     * @param callable $timeExpression
     */
    public function setTimeCycle(callable $timeExpression);

    /**
     * Set the duration expression for the timer event definition.
     *
     * @param callable $timeExpression
     */
    public function setTimeDuration(callable $timeExpression);

    /**
     * Register in catch events.
     *
     * @param EngineInterface $engine
     * @param FlowElementInterface $element
     * @param TokenInterface|null $token
     */
    public function registerCatchEvents(EngineInterface $engine, FlowElementInterface $element, TokenInterface $token = null);
}
