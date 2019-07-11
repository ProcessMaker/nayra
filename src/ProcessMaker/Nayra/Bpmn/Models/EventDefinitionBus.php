<?php

namespace ProcessMaker\Nayra\Bpmn\Models;

use ProcessMaker\Nayra\Bpmn\ObservableTrait;
use ProcessMaker\Nayra\Contracts\Bpmn\CatchEventInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\CollaborationInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\EventDefinitionInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\MessageEventDefinitionInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\StartEventInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\ThrowEventInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\TokenInterface;
use ProcessMaker\Nayra\Contracts\Engine\EventDefinitionBusInterface;

/**
 * Implements a events bus for the bpmn elements.
 *
 */
class EventDefinitionBus implements EventDefinitionBusInterface
{
    use ObservableTrait;

    private $collaboration;

    /**
     * Dispatch an event definition
     *
     * @param ThrowEventInterface $source
     * @param EventDefinitionInterface $eventDefinition
     * @param TokenInterface $token
     *
     * @return EventDefinitionBusInterface
     */
    public function dispatchEventDefinition(ThrowEventInterface $source, EventDefinitionInterface $eventDefinition, TokenInterface $token)
    {
        $this->notifyEvent(get_class($eventDefinition), $source, $eventDefinition, $token);
        return $this;
    }

    /**
     * Register a catch event element
     *
     * @param CatchEventInterface $catchEvent
     * @param EventDefinitionInterface $eventDefinition
     * @param callable $callable
     *
     * @return EventDefinitionBusInterface
     */
    public function registerCatchEvent(CatchEventInterface $catchEvent, EventDefinitionInterface $eventDefinition, callable $callable)
    {
        $this->attachEvent(get_class($eventDefinition), function (ThrowEventInterface $source, EventDefinitionInterface $sourceEventDefinition, TokenInterface $token) use ($catchEvent, $callable, $eventDefinition) {
            if (get_class($sourceEventDefinition) === get_class($eventDefinition)) {
                $match = $eventDefinition->shouldCatchEventDefinition($sourceEventDefinition);
                if ($match && $eventDefinition instanceof MessageEventDefinitionInterface) {
                    $matchMessageFlow = false;
                    foreach ($this->getCollaboration()->getMessageFlows() as $messageFlow) {
                        $matchMessageFlow = $matchMessageFlow ||
                         ($messageFlow->getSource()->getId() === $source->getId() &&
                         $messageFlow->getTarget()->getId() === $catchEvent->getId());
                    }
                    $match = $match && $matchMessageFlow;
                }
                if ($match && $catchEvent instanceof StartEventInterface) {
                    $callable($eventDefinition, null, $token);
                } elseif ($match) {
                    $instances = $this->getInstancesFor($catchEvent);
                    foreach ($instances as $instance) {
                        $callable($eventDefinition, $instance, $token);
                    }
                }
            }
        });
        return $this;
    }

    /**
     * Set collaboration
     *
     * @param CollaborationInterface $collaboration
     *
     * @return EventDefinitionBusInterface
     */
    public function setCollaboration(CollaborationInterface $collaboration)
    {
        $this->collaboration = $collaboration;
        return $this;
    }

    /**
     * Get collaboration
     *
     * @return CollaborationInterface
     */
    public function getCollaboration()
    {
        return $this->collaboration;
    }

    /**
     * Get instances for a catch event
     *
     * @param CatchEventInterface $catchEvent
     *
     * @return \ProcessMaker\Nayra\Bpmn\Collection
     */
    private function getInstancesFor(CatchEventInterface $catchEvent)
    {
        return $catchEvent->getProcess()->getInstances();
    }
}
