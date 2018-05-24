<?php
namespace ProcessMaker\Nayra\Bpmn;
use ProcessMaker\Nayra\Contracts\Bpmn\EventDefinitionInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\FlowElementInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\FlowNodeInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\FormalExpressionInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\TimerEventDefinitionInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\TokenInterface;
use ProcessMaker\Nayra\Contracts\Engine\EngineInterface;
use ProcessMaker\Nayra\Contracts\Engine\ExecutionInstanceInterface;
use ProcessMaker\Nayra\Contracts\Engine\JobManagerInterface;
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
     *
     * @codeCoverageIgnore Until intermediate timer event implementation
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
    /**
     * Register in catch events.
     *
     * @param EngineInterface $engine
     * @param FlowElementInterface $element
     */
    public function registerCatchEvents(EngineInterface $engine, FlowElementInterface $element, $token)
    {
        $this->scheduleTimeDuration($engine, $element, $token);
        $this->scheduleTimeDate($engine, $element, $token);
        $this->scheduleTimeCycle($engine, $element, $token);
    }
    /**
     * Get the data store.
     *
     * @param EngineInterface $engine
     * @param TokenInterface $token
     *
     * @return \ProcessMaker\Nayra\Contracts\Bpmn\DataStoreInterface
     */
    private function getDataFrom(EngineInterface $engine, TokenInterface $token = null)
    {
        $dataStore = $token ? $token->getInstance()->getDataStore() : $engine->getDataStore();
        return $dataStore ? $dataStore->getData() : [];
    }
    /**
     * Schedule as timeDate.
     *
     * @param EngineInterface $engine
     * @param FlowElementInterface $element
     * @param TokenInterface $token
     */
    private function scheduleTimeDate(EngineInterface $engine, FlowElementInterface $element, TokenInterface $token = null)
    {
        $expression = $this->getTimeDate();
        if ($expression) {
            $date = $expression($this->getDataFrom($engine, $token));
            $engine->getDispatcher()->dispatch(
                JobManagerInterface::EVENT_SCHEDULE_DATE,
                $date,
                $this,
                $element,
                $token
            );
        }
    }
    /**
     * Schedule as timeDate.
     *
     * @param EngineInterface $engine
     * @param FlowElementInterface $element
     * @param TokenInterface $token
     */
    private function scheduleTimeCycle(EngineInterface $engine, FlowElementInterface $element, TokenInterface $token = null)
    {
        $expression = $this->getTimeCycle();
        if ($expression) {
            $cycle = $expression($this->getDataFrom($engine, $token));
            $engine->getDispatcher()->dispatch(
                JobManagerInterface::EVENT_SCHEDULE_CYCLE,
                $cycle,
                $this,
                $element,
                $token
            );
        }
    }
    /**
     * Schedule as timeDuration.
     *
     * @param EngineInterface $engine
     * @param FlowElementInterface $element
     * @param TokenInterface $token
     *
     * @codeCoverageIgnore Until intermediate timer event implementation
     */
    private function scheduleTimeDuration(EngineInterface $engine, FlowElementInterface $element, TokenInterface $token = null)
    {
        $expression = $this->getTimeDuration();
        if ($expression) {
            $duration = $expression($this->getDataFrom($engine, $token));
            $engine->getDispatcher()->dispatch(
                JobManagerInterface::EVENT_SCHEDULE_DURATION,
                $duration,
                $this,
                $element,
                $token
            );
        }
    }

    /**
     * Set the date expression for the timer event definition.
     *
     * @param Callable $timeExpression
     */
    public function setTimeDate(callable $timeExpression)
    {
        $this->setProperty(self::BPMN_PROPERTY_TIME_DATE, $timeExpression);
    }

    /**
     * Set the cycle expression for the timer event definition.
     *
     * @param Callable $timeExpression
     */
    public function setTimeCycle(callable $timeExpression)
    {
        $this->setProperty(self::BPMN_PROPERTY_TIME_CYCLE, $timeExpression);
    }

    /**
     * Set the duration expression for the timer event definition.
     *
     * @param Callable $timeExpression
     */
    public function setTimeDuration(callable $timeExpression)
    {
        $this->setProperty(self::BPMN_PROPERTY_TIME_DURATION, $timeExpression);
    }
}

