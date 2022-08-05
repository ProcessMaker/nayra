<?php

namespace ProcessMaker\Nayra\Contracts\Bpmn;

use ProcessMaker\Nayra\Contracts\Engine\EngineInterface;
use ProcessMaker\Nayra\Contracts\Engine\ExecutionInstanceInterface;

/**
 * EventDefinition interface.
 */
interface EventDefinitionInterface extends EntityInterface
{
    /**
     * Returns the element's id
     *
     * @return mixed
     */
    public function getId();

    /**
     * Sets the element id
     *
     * @param mixed $value
     */
    public function setId($value);

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
    public function assertsRule(self $event, FlowNodeInterface $target, ExecutionInstanceInterface $instance = null, TokenInterface $token = null);

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
    public function execute(self $event, FlowNodeInterface $target, ExecutionInstanceInterface $instance = null, TokenInterface $token = null);

    /**
     * Register event with a catch event
     *
     * @param EngineInterface $engine
     * @param CatchEventInterface $element
     *
     * @return void
     */
    public function registerWithCatchEvent(EngineInterface $engine, CatchEventInterface $element);

    /**
     * Occurs when the catch event was activated
     *
     * @param EngineInterface $engine
     * @param CatchEventInterface $element
     * @param TokenInterface|null $token
     *
     * @return void
     */
    public function catchEventActivated(EngineInterface $engine, CatchEventInterface $element, TokenInterface $token = null);

    /**
     * Check if the event definition should be catched
     *
     * @param EventDefinitionInterface $sourceEvent
     *
     * @return bool
     */
    public function shouldCatchEventDefinition(self $sourceEvent);

    /**
     * Get data contained in the event payload
     *
     * @param TokenInterface|null $token
     * @param CatchEventInterface|null $target
     *
     * @return mixed
     */
    public function getPayloadData(TokenInterface $token = null, CatchEventInterface $target = null);

    /**
     * Set do not trigger start events
     *
     * @param bool $value
     *
     * @return $this
     */
    public function setDoNotTriggerStartEvents($value);

    /**
     * Get do not trigger start events value
     *
     * @return bool
     */
    public function getDoNotTriggerStartEvents();

    /**
     * Returns the event of the event definition (message, signal, etc.)
     *
     * @return SignalInterface|MessageInterface|ErrorInterface|mixed
     */
    public function getPayload();
}
