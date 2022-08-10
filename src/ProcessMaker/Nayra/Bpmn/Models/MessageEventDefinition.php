<?php

namespace ProcessMaker\Nayra\Bpmn\Models;

use ProcessMaker\Nayra\Bpmn\EventDefinitionTrait;
use ProcessMaker\Nayra\Contracts\Bpmn\EventDefinitionInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\FlowNodeInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\MessageEventDefinitionInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\MessageInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\OperationInterface;
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
        return $this;
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
