<?php

namespace ProcessMaker\Nayra\Bpmn\Models;

use ProcessMaker\Nayra\Bpmn\BaseTrait;
use ProcessMaker\Nayra\Bpmn\Collection;
use ProcessMaker\Nayra\Contracts\Bpmn\MessageInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\OperationInterface;

/**
 * Implementation of the operation class.
 *
 */
class Operation implements OperationInterface
{
    use BaseTrait;

    /**
     * This attribute allows to reference a concrete artifact in the underlying
     * implementation technology representing that operation.
     *
     * @return callback
     */
    public function getImplementation()
    {
        return $this->getProperty(OperationInterface::BPMN_PROPERTY_IMPLEMENTATION);
    }

    /**
     * Get the input Message of the Operation.
     *
     * @return MessageInterface
     */
    public function getInMessage()
    {
        return $this->getProperty(OperationInterface::BPMN_PROPERTY_IN_MESSAGE);
    }

    /**
     * Get the output Message of the Operation.
     *
     * @return MessageInterface
     */
    public function getOutMessage()
    {
        return $this->getProperty(OperationInterface::BPMN_PROPERTY_OUT_MESSAGE);
    }

    /**
     * Get errors that the Operation may return.
     *
     * @return mixed[]
     */
    public function getErrors()
    {
        return $this->getProperty(OperationInterface::BPMN_PROPERTY_ERRORS, new Collection);
    }
}
