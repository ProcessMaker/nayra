<?php

namespace ProcessMaker\Nayra\Contracts\Bpmn;

/**
 * MessageEventDefinition interface.
 *
 * @package ProcessMaker\Nayra\Contracts\Bpmn
 */
interface MessageEventDefinitionInterface extends EventDefinitionInterface
{

    /**
     * Get the message.
     *
     * @return MessageInterface
     */
    public function getMessage();

    /**
     * Get the Operation that is used by the Message Event.
     *
     * @return OperationInterface
     */
    public function getOperation();

    /**
     * Get the message.
     *
     * @param MessageInterface $message
     *
     * @return $this
     */
    public function setMessage(MessageInterface $message);

    /**
     * Get the Operation that is used by the Message Event.
     *
     * @param OperationInterface $operation
     *
     * @return $this
     */
    public function setOperation(OperationInterface $operation);
}
