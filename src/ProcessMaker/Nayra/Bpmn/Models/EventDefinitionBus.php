<?php

namespace ProcessMaker\Nayra\Bpmn\Models;

use ProcessMaker\Nayra\Bpmn\ObservableTrait;
use ProcessMaker\Nayra\Contracts\Bpmn\CatchEventInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\CollaborationInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\EventDefinitionInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\StartEventInterface;
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
     * @param mixed $source
     * @param EventDefinitionInterface $eventDefinition
     * @param TokenInterface|null $token
     *
     * @return EventDefinitionBusInterface
     */
    public function dispatchEventDefinition($source, EventDefinitionInterface $eventDefinition, TokenInterface $token = null)
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
        $this->attachEvent(get_class($eventDefinition), function ($source, EventDefinitionInterface $sourceEventDefinition, TokenInterface $token = null) use ($catchEvent, $callable, $eventDefinition) {
            if (get_class($sourceEventDefinition) === get_class($eventDefinition)) {
                $match = $eventDefinition->shouldCatchEventDefinition($sourceEventDefinition);
                if (!$match) {
                    return;
                }
                if ($catchEvent instanceof StartEventInterface && $sourceEventDefinition->getDoNotTriggerStartEvents()) {
                    return;
                }
                if ($catchEvent instanceof StartEventInterface) {
                    $callable($eventDefinition, null, $token);
                } else {
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
