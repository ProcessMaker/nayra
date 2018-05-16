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
}
