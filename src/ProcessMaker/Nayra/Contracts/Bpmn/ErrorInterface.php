<?php

namespace ProcessMaker\Nayra\Contracts\Bpmn;

/**
 * ErrorInterface for the ErrorEventDefinition.
 *
 * @package ProcessMaker\Nayra\Contracts\Bpmn
 */
interface ErrorInterface extends EntityInterface
{
    const BPMN_PROPERTY_ERROR_CODE = 'errorCode';

    /**
     * Get the error code of the ErrorEventDefinition
     *
     * @return string
     */
    public function getErrorCode();

    /**
     * Returns the name of the message
     *
     * @return string
     */
    public function getName();
}
