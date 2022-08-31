<?php

namespace ProcessMaker\Nayra\Bpmn\Models;

use ProcessMaker\Nayra\Bpmn\EventDefinitionTrait;
use ProcessMaker\Nayra\Contracts\Bpmn\CatchEventInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\EventDefinitionInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\FlowElementInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\FlowNodeInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\TimerEventDefinitionInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\TokenInterface;
use ProcessMaker\Nayra\Contracts\Engine\EngineInterface;
use ProcessMaker\Nayra\Contracts\Engine\ExecutionInstanceInterface;
use ProcessMaker\Nayra\Contracts\Engine\JobManagerInterface;

/**
 * MessageEventDefinition class
 */
class TimerEventDefinition implements TimerEventDefinitionInterface
{
    use EventDefinitionTrait;

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
     * @param ExecutionInstanceInterface|null $instance
     * @param TokenInterface|null $token
     *
     * @return bool
     */
    public function assertsRule(EventDefinitionInterface $event, FlowNodeInterface $target, ExecutionInstanceInterface $instance = null, TokenInterface $token = null)
    {
        return true;
    }

    /**
     * Occures when the catch event was activated
     *
     * @param EngineInterface $engine
     * @param CatchEventInterface $element
     * @param TokenInterface|null $token
     *
     * @return void
     */
    public function catchEventActivated(EngineInterface $engine, CatchEventInterface $element, TokenInterface $token = null)
    {
        $this->scheduleTimerEvents($engine, $element, $token);
    }

    /**
     * Register in catch events.
     *
     * @param EngineInterface $engine
     * @param FlowElementInterface $element
     * @param TokenInterface|null $token
     */
    public function scheduleTimerEvents(EngineInterface $engine, FlowElementInterface $element, TokenInterface $token = null)
    {
        $this->scheduleTimeDuration($engine, $element, $token);
        $this->scheduleTimeDate($engine, $element, $token);
        $this->scheduleTimeCycle($engine, $element, $token);
    }

    /**
     * Get the data store.
     *
     * @param EngineInterface $engine
     * @param TokenInterface|null $token
     *
     * @return \ProcessMaker\Nayra\Contracts\Bpmn\DataStoreInterface
     */
    private function getDataFrom(EngineInterface $engine, TokenInterface $token = null)
    {
        $dataStore = $token ? $token->getInstance()->getDataStore() : $engine->getDataStore();

        return $dataStore ? $dataStore->getData() : [];
    }

    /**
     * Evaluates a timer expression.
     *
     * @param callable $expression
     * @param array $data
     *
     * @return DateTime|DatePeriod|DateInterval
     */
    private function evaluateTimer(callable $expression, array $data)
    {
        $value = $expression($data);
        if (is_string($value)) {
            $formal = $this->getRepository()->createFormalExpression();
            $formal->setProperty('body', $value);

            return $formal($data);
        }

        return $value;
    }

    /**
     * Schedule as timeDate.
     *
     * @param EngineInterface $engine
     * @param FlowElementInterface $element
     * @param TokenInterface|null $token
     */
    private function scheduleTimeDate(EngineInterface $engine, FlowElementInterface $element, TokenInterface $token = null)
    {
        $expression = $this->getTimeDate();
        if ($expression) {
            $date = $this->evaluateTimer($expression, $this->getDataFrom($engine, $token));
            $dates = is_array($date) ? $date : [$date];
            foreach ($dates as $date) {
                $engine->getJobManager()->scheduleDate($date, $this, $element, $token);
                $engine->getDispatcher()->dispatch(
                    JobManagerInterface::EVENT_SCHEDULE_DATE,
                    $date,
                    $this,
                    $element,
                    $token
                );
            }
        }
    }

    /**
     * Schedule as timeDate.
     *
     * @param EngineInterface $engine
     * @param FlowElementInterface $element
     * @param TokenInterface|null $token
     */
    private function scheduleTimeCycle(EngineInterface $engine, FlowElementInterface $element, TokenInterface $token = null)
    {
        $expression = $this->getTimeCycle();
        if ($expression) {
            $cycle = $this->evaluateTimer($expression, $this->getDataFrom($engine, $token));
            $cycles = is_array($cycle) ? $cycle : [$cycle];
            foreach ($cycles as $cycle) {
                $engine->getJobManager()->scheduleCycle($cycle, $this, $element, $token);
                $engine->getDispatcher()->dispatch(
                    JobManagerInterface::EVENT_SCHEDULE_CYCLE,
                    $cycle,
                    $this,
                    $element,
                    $token
                );
            }
        }
    }

    /**
     * Schedule as timeDuration.
     *
     * @param EngineInterface $engine
     * @param FlowElementInterface $element
     * @param TokenInterface|null $token
     */
    private function scheduleTimeDuration(EngineInterface $engine, FlowElementInterface $element, TokenInterface $token = null)
    {
        $expression = $this->getTimeDuration();
        if ($expression) {
            $duration = $this->evaluateTimer($expression, $this->getDataFrom($engine, $token));
            $durations = is_array($duration) ? $duration : [$duration];
            foreach ($durations as $duration) {
                $engine->getJobManager()->scheduleDuration($duration, $this, $element, $token);
                $engine->getDispatcher()->dispatch(
                    JobManagerInterface::EVENT_SCHEDULE_DURATION,
                    $duration,
                    $this,
                    $element,
                    $token
                );
            }
        }
    }

    /**
     * Set the date expression for the timer event definition.
     *
     * @param callable $timeExpression
     */
    public function setTimeDate(callable $timeExpression)
    {
        $this->setProperty(self::BPMN_PROPERTY_TIME_DATE, $timeExpression);
    }

    /**
     * Set the cycle expression for the timer event definition.
     *
     * @param callable $timeExpression
     */
    public function setTimeCycle(callable $timeExpression)
    {
        $this->setProperty(self::BPMN_PROPERTY_TIME_CYCLE, $timeExpression);
    }

    /**
     * Set the duration expression for the timer event definition.
     *
     * @param callable $timeExpression
     */
    public function setTimeDuration(callable $timeExpression)
    {
        $this->setProperty(self::BPMN_PROPERTY_TIME_DURATION, $timeExpression);
    }

    /**
     * Implement the event definition behavior when an event is triggered.
     *
     * @param EventDefinitionInterface $event
     * @param FlowNodeInterface $target
     * @param ExecutionInstanceInterface|null $instance
     * @param TokenInterface|null $token
     *
     * @return $this
     */
    public function execute(EventDefinitionInterface $event, FlowNodeInterface $target, ExecutionInstanceInterface $instance = null, TokenInterface $token = null)
    {
        return $this;
    }
}
