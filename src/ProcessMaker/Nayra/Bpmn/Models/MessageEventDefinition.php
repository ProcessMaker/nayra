<?php

namespace ProcessMaker\Nayra\Bpmn\Models;

use ProcessMaker\Nayra\Bpmn\BaseTrait;
use ProcessMaker\Nayra\Contracts\Bpmn\EventDefinitionInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\FlowNodeInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\MessageEventDefinitionInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\MessageInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\OperationInterface;
use ProcessMaker\Nayra\Contracts\Engine\ExecutionInstanceInterface;

/**
 * MessageEventDefinition class
 *
 */
class MessageEventDefinition implements MessageEventDefinitionInterface
{
    use BaseTrait;

    /**
     * @var string $id
     */
    private $id;

    /**
     * @var \ProcessMaker\Nayra\Contracts\Bpmn\MessageInterface $message
     */
    private $message;

    /**
     * @var \ProcessMaker\Nayra\Contracts\Bpmn\OperationInterface $operation
     */
    private $operation;

    /**
     * Get the message.
     *
     * @return MessageInterface
     */
    public function getPayload()
    {
        return $this->message;
    }

    /**
     * Sets the message to be used in the message event definition
     *
     * @param MessageInterface $message
     * @return $this
     */
    public function setPayload($message)
    {
        $this->message = $message;
        return $this;
    }

    /**
     * Get the Operation that is used by the Message Event.
     *
     * @return OperationInterface
     */
    public function getOperation()
    {
        return $this->operation;
    }

    /**
     * Sets the operation of the message event definition
     *
     * @param OperationInterface $operation
     * @return $this
     */
    public function setOperation(OperationInterface $operation)
    {
        $this->operation = $operation;
        return $this;
    }

    /**
     * Returns the element's id
     *
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Sets the element id
     *
     * @param $value
     * @return mixed
     */
    public function setId($value)
    {
        $this->id = $value;
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
