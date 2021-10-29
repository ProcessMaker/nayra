<?php

namespace ProcessMaker\Nayra\Contracts\Bpmn;

use ProcessMaker\Nayra\Contracts\Engine\EngineInterface;
use ProcessMaker\Nayra\Contracts\Engine\ExecutionInstanceInterface;

/**
 * EventDefinition interface.
 *
 * @package ProcessMaker\Nayra\Contracts\Bpmn
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
     * @return boolean
     */
    public function assertsRule(EventDefinitionInterface $event, FlowNodeInterface $target, ExecutionInstanceInterface $instance = null, TokenInterface $token = null);

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
    public function execute(EventDefinitionInterface $event, FlowNodeInterface $target, ExecutionInstanceInterface $instance = null, TokenInterface $token = null);

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
     * Occures when the catch event was activated
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
    public function shouldCatchEventDefinition(EventDefinitionInterface $sourceEvent);

    /**
     * Get data contained in the event payload
     *
     * @param TokenInterface|null $token
     *
     * @return mixed
     */
    public function getPayloadData(TokenInterface $token = null);
}
