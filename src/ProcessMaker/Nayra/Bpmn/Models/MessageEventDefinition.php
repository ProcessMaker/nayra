<?php

namespace ProcessMaker\Nayra\Bpmn\Models;

use ProcessMaker\Nayra\Bpmn\EventDefinitionTrait;
use ProcessMaker\Nayra\Contracts\Bpmn\CatchEventInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\CollectionInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\DataStoreInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\EventDefinitionInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\FlowNodeInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\MessageEventDefinitionInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\MessageInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\OperationInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\ThrowEventInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\TokenInterface;
use ProcessMaker\Nayra\Contracts\Engine\ExecutionInstanceInterface;

/**
 * MessageEventDefinition class
 */
class MessageEventDefinition implements MessageEventDefinitionInterface
{
    use EventDefinitionTrait;

    /**
     * Get the message.
     *
     * @return MessageInterface
     */
    public function getPayload()
    {
        return $this->getProperty(static::BPMN_PROPERTY_MESSAGE);
    }

    /**
     * Sets the message to be used in the message event definition
     *
     * @param MessageInterface $message
     *
     * @return $this
     */
    public function setPayload(MessageInterface $message)
    {
        return $this->setProperty(static::BPMN_PROPERTY_MESSAGE, $message);
    }

    /**
     * Get the Operation that is used by the Message Event.
     *
     * @return OperationInterface
     */
    public function getOperation()
    {
        return $this->getProperty(static::BPMN_PROPERTY_OPERATION);
    }

    /**
     * Sets the operation of the message event definition
     *
     * @param OperationInterface $operation
     * @return $this
     */
    public function setOperation(OperationInterface $operation)
    {
        return $this->setProperty(static::BPMN_PROPERTY_OPERATION, $operation);
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
        if ($token !== null) {
            $throwEvent = $token->getOwnerElement();
            if ($throwEvent instanceof ThrowEventInterface && $target instanceof CatchEventInterface) {
                $this->executeMessageMapping($throwEvent, $target, $instance, $token);
            }
        }

        return $this;
    }

    /**
     * Map a message payload from a ThrowEvent through an optional CatchEvent mapping
     * into the instance data store.
     *
     * @param ThrowEventInterface $throwEvent
     * @param CatchEventInterface $catchEvent
     * @param ExecutionInstanceInterface $instance
     * @param TokenInterface $token
     *
     * @return void
     */
    private function executeMessageMapping(ThrowEventInterface $throwEvent, CatchEventInterface $catchEvent, ExecutionInstanceInterface $instance, TokenInterface $token): void
    {
        $sourceMaps = $throwEvent->getDataInputAssociations();
        $targetMaps = $catchEvent->getDataOutputAssociations();
        $targetStore = $instance->getDataStore();

        // Source of data is the token's instance store if present; otherwise a fresh store.
        $sourceStore = $token->getInstance()?->getDataStore() ?? new DataStore();

        // If target mappings exist we stage into a buffer; otherwise write straight to the instance store.
        $bufferStore = !count($targetMaps) ? $targetStore : new DataStore();

        // 1) Source mappings: source → buffer/instance
        $this->evaluateMessagePayload($sourceMaps, $sourceStore, $bufferStore);

        // 2) Optional target mappings: buffer → instance
        if (count($targetMaps)) {
            $this->evaluateMessagePayload($targetMaps, $bufferStore, $targetStore);
        }
    }

    /**
     * Evaluate the message payload
     *
     * @param CollectionInterface $associations
     * @param DataStoreInterface $sourceStore
     * @param DataStoreInterface $targetStore
     *
     * @return void
     */
    private function evaluateMessagePayload(CollectionInterface $associations, DataStoreInterface $sourceStore, DataStoreInterface $targetStore): void
    {
        $assignments = [];

        foreach ($associations as $association) {
            $source = $association->getSource();
            $target = $association->getTarget();

            $hasSource = $source && $source->getName();
            $hasTarget = $target && $target->getName();

            // Base data always starts from full source store
            $data = $sourceStore->getData();

            // Optionally add a direct reference to the source value
            if ($hasSource) {
                $data['sourceRef'] = $sourceStore->getDotData($source->getName());
            }

            // Transformation and assignments build up the assignments list
            $this->applyTransformation($association, $data, $assignments, $hasTarget, $hasSource);
            $this->evaluateAssignments($association, $data, $assignments);
        }

        // Flush all assignments into target store
        foreach ($assignments as $assignment) {
            $targetStore->setDotData($assignment['key'], $assignment['value']);
        }
    }

    /**
     * Apply transformation to the data and add to payload
     *
     * @param mixed $association
     * @param array $data
     * @param array &$payload
     * @param bool $hasTarget
     * @param bool $hasSource
     */
    private function applyTransformation($association, array $data, array &$payload, bool $hasTarget, bool $hasSource)
    {
        $transformation = $association->getTransformation();
        $target = $association->getTarget();

        if ($hasTarget && $transformation && is_callable($transformation)) {
            $value = $transformation($data);
            $payload[] = ['key' => $target->getName(), 'value' => $value];
        } elseif ($hasTarget && $hasSource) {
            $payload[] = ['key' => $target->getName(), 'value' => $data['sourceRef']];
        }
    }

    /**
     * Evaluate assignments and add to payload
     *
     * @param mixed $association
     * @param array $data
     * @param array &$payload
     */
    private function evaluateAssignments($association, array $data, array &$payload)
    {
        $assignments = $association->getAssignments();
        foreach ($assignments as $assignment) {
            $from = $assignment->getFrom();
            $to = trim($assignment->getTo()?->getBody());
            if (is_callable($from) && !empty($to)) {
                $payload[] = ['key' => $to, 'value' => $from($data)];
            }
        }
    }

    /**
     * Check if the $eventDefinition should be catch
     *
     * @param EventDefinitionInterface $eventDefinition
     *
     * @return bool
     */
    public function shouldCatchEventDefinition(EventDefinitionInterface $eventDefinition)
    {
        $targetPayload = $this->getPayload();
        $sourcePayload = $eventDefinition->getPayload();

        return (!$targetPayload && !$sourcePayload)
            || ($targetPayload && $sourcePayload && $targetPayload->getId() === $sourcePayload->getId());
    }
}
