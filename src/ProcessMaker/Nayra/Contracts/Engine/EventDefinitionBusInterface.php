<?php

namespace ProcessMaker\Nayra\Contracts\Engine;

use ProcessMaker\Nayra\Contracts\Bpmn\CatchEventInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\CollaborationInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\EventDefinitionInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\ObservableInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\ThrowEventInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\TokenInterface;

/**
 * An event definition bus interface for the bpmn elements
 */
interface EventDefinitionBusInterface extends ObservableInterface
{
    /**
     * Dispatch an event definition
     *
     * @param ThrowEventInterface $source
     * @param EventDefinitionInterface $eventDefinition
     * @param TokenInterface $token
     *
     * @return void
     */
    public function dispatchEventDefinition(ThrowEventInterface $source, EventDefinitionInterface $eventDefinition, TokenInterface $token);

    /**
     * Register a catch event
     *
     * @param CatchEventInterface $catchEvent
     * @param EventDefinitionInterface $eventDefinition
     * @param callable $callable
     *
     * @return void
     */
    public function registerCatchEvent(CatchEventInterface $catchEvent, EventDefinitionInterface $eventDefinition, callable $callable);

    /**
     * Set collaboration
     *
     * @param CollaborationInterface $collaboration
     *
     * @return EventDefinitionBusInterface
     */
    public function setCollaboration(CollaborationInterface $collaboration);

    /**
     * Get collaboration
     *
     * @return CollaborationInterface
     */
    public function getCollaboration();
}
