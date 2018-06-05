<?php

namespace ProcessMaker\Nayra\Contracts\Bpmn;

/**
 * Operation interface.
 *
 * @package ProcessMaker\Nayra\Contracts\Bpmn
 */
interface OperationInterface extends EntityInterface
{
    const BPMN_TAG = 'operation';
    const BPMN_PROPERTY_IMPLEMENTATION = 'implementation';
    const BPMN_PROPERTY_IN_MESSAGE ='inMessage';
    const BPMN_PROPERTY_OUT_MESSAGE ='outMessage';
    const BPMN_PROPERTY_ERRORS ='errors';

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
