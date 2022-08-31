<?php

namespace ProcessMaker\Nayra\Contracts\Bpmn;

/**
 * Operation interface.
 */
interface OperationInterface extends EntityInterface
{
    const BPMN_TAG = 'operation';

    const BPMN_PROPERTY_IMPLEMENTATION = 'implementationRef';

    const BPMN_PROPERTY_IN_MESSAGE = 'inMessage';

    const BPMN_PROPERTY_IN_MESSAGE_REF = 'inMessageRef';

    const BPMN_PROPERTY_OUT_MESSAGE = 'outMessage';

    const BPMN_PROPERTY_OUT_MESSAGE_REF = 'outMessageRef';

    const BPMN_PROPERTY_ERRORS = 'errors';

    const BPMN_PROPERTY_ERROR_REF = 'errorRef';

    /**
     * This attribute allows to reference a concrete artifact in the underlying
     * implementation technology representing that operation.
     *
     * @return callable
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
