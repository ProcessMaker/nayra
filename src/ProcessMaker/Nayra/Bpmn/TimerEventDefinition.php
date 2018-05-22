<?php

namespace ProcessMaker\Nayra\Bpmn;

use ProcessMaker\Nayra\Contracts\Bpmn\EventDefinitionInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\FlowNodeInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\TimerEventDefinitionInterface;
use ProcessMaker\Nayra\Contracts\Engine\ExecutionInstanceInterface;

/**
 * MessageEventDefinition class
 *
 */
class TimerEventDefinition implements TimerEventDefinitionInterface
{
    use BaseTrait;


    /**
     * Get the date expression for the timer event definition.
     *
     * @return \ProcessMaker\Nayra\Contracts\Bpmn\FormalExpressionInterface
     */
    public function getTimeDate()
    {
        return $this->getProperty(self::BPMN_PROPERTY_TIME_DATE);
    }

    /**
     * Get the cycle expression for the timer event definition.
     *
     * @return \ProcessMaker\Nayra\Contracts\Bpmn\FormalExpressionInterface
     */
    public function getTimeCycle()
    {
        return $this->getProperty(self::BPMN_PROPERTY_TIME_CYCLE);
    }

    /**
     * Get the duration expression for the timer event definition.
     *
     * @return \ProcessMaker\Nayra\Contracts\Bpmn\FormalExpressionInterface
     */
    public function getTimeDuration()
    {
        return $this->getProperty(self::BPMN_PROPERTY_TIME_DURATION);
    }

    /**
     * Assert the event definition rule for trigger the event.
     *
     * @param EventDefinitionInterface $event
     * @param FlowNodeInterface $target
     * @param ExecutionInstanceInterface $instance
     *
     * @return boolean
     */
    public function assertsRule(EventDefinitionInterface $event, FlowNodeInterface $target, ExecutionInstanceInterface $instance = null)
    {
        return true;
    }
}
