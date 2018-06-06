<?php

namespace ProcessMaker\Nayra\Contracts\Bpmn;

/**
 * MessageEventDefinition interface.
 *
 * @package ProcessMaker\Nayra\Contracts\Bpmn
 */
interface MessageEventDefinitionInterface extends EventDefinitionInterface
{

    const EVENT_THROW_EVENT_DEFINITION = 'ThrowMessageEvent';
    const EVENT_CATCH_EVENT_DEFINITION = 'CatchMessageEvent';
    const BPMN_PROPERTY_OPERATION = 'operationRef';
    const BPMN_PROPERTY_OPERATION_REF = 'operationRef';
    const BPMN_PROPERTY_MESSAGE = 'message';
    const BPMN_PROPERTY_MESSAGE_REF = 'messageRef';

    /**
     * Get the Operation that is used by the Message Event.
     *
     * @return OperationInterface
     */
    public function getOperation();

    /**
     * Get the Operation that is used by the Message Event.
     *
     * @param OperationInterface $operation
     *
     * @return $this
     */
    public function setOperation(OperationInterface $operation);

    /**
     * Returns the event definition payload (message, signal, etc.)
     *
     * @return mixed
     */
    public function getPayload();

    /**
     * Sets the message to be used in the message event definition
     *
     * @param MessageInterface $message
     *
     * @return $this
     */
    public function setPayload(MessageInterface $message);
}
