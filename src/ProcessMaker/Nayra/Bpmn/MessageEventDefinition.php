<?php

namespace ProcessMaker\Nayra\Bpmn;

use ProcessMaker\Nayra\Contracts\Bpmn\MessageEventDefinitionInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\MessageInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\OperationInterface;

/**
 * MessageEventDefinition class
 *
 */
class MessageEventDefinition implements MessageEventDefinitionInterface
{
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

    private $collaboration;

    /**
     * Get the message.
     *
     * @return MessageInterface
     */
    public function getMessage()
    {
        return $this->message;
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
     * Sets the message to be used in the message event definition
     *
     * @param MessageInterface $message
     * @return $this
     */
    public function setMessage(MessageInterface $message)
    {
        $this->message = $message;
        return $this;
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
}
