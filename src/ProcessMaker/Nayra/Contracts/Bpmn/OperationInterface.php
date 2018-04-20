<?php

namespace ProcessMaker\Nayra\Contracts\Bpmn;

/**
 * Operation interface.
 *
 * @package ProcessMaker\Nayra\Contracts\Bpmn
 */
interface OperationInterface extends EntityInterface
{

    /**
     * This attribute allows to reference a concrete artifact in the underlying
     * implementation technology representing that operation.
     *
     * @return callback
     */
    public function getImplementation();

    /**
     * Get the input Message of the Operation.
     *
     * @return MessageInterface
     */
    public function getInMessage();

    /**
     * Get the output Message of the Operation.
     *
     * @return MessageInterface
     */
    public function getOutMessage();

    /**
     * Get errors that the Operation may return.
     *
     * @return mixed[]
     */
    public function getErrors();
}
