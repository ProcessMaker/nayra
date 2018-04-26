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
}
