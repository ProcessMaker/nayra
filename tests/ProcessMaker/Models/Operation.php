<?php

namespace ProcessMaker\Models;

use ProcessMaker\Nayra\Contracts\Bpmn\EntityInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\MessageInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\OperationInterface;
use ProcessMaker\Nayra\Contracts\Repositories\RepositoryFactoryInterface;

class Operation implements OperationInterface
{

    /**
     *
     * @param array $properties
     */
    public function setProperties(array $properties)
    {
        // TODO: Implement setProperties() method.
    }

    /**
     *
     * @return array
     */
    public function getProperties()
    {
        // TODO: Implement getProperties() method.
    }

    /**
     *
     * @param $name
     * @param $value
     *
     * @return $this
     */
    public function setProperty($name, $value)
    {
        // TODO: Implement setProperty() method.
    }

    /**
     *
     * @param $name
     * @param mixed $default
     *
     * @return mixed
     */
    public function getProperty($name, $default = null)
    {
        // TODO: Implement getProperty() method.
    }

    /**
     * @return \ProcessMaker\Nayra\Contracts\Repositories\RepositoryFactoryInterface
     */
    public function getFactory()
    {
        // TODO: Implement getFactory() method.
    }

    /**
     * @param RepositoryFactoryInterface $factory
     * @return $this
     */
    public function setFactory(RepositoryFactoryInterface $factory)
    {
        // TODO: Implement setFactory() method.
    }

    /**
     * Get Entity ID
     *
     * @return mixed
     */
    public function getId()
    {
        // TODO: Implement getId() method.
    }

    /**
     * Set Entity ID
     *
     * @return mixed
     */
    public function setId($id)
    {
        // TODO: Implement setId() method.
    }

    /**
     * Load custom properties from an array.
     *
     * @param array $customProperties
     *
     * @return $this
     */
    public function loadCustomProperties(array $customProperties)
    {
        // TODO: Implement loadCustomProperties() method.
    }

    /**
     * This attribute allows to reference a concrete artifact in the underlying
     * implementation technology representing that operation.
     *
     * @return callback
     */
    public function getImplementation()
    {
        // TODO: Implement getImplementation() method.
    }

    /**
     * Get the input Message of the Operation.
     *
     * @return MessageInterface
     */
    public function getInMessage()
    {
        // TODO: Implement getInMessage() method.
    }

    /**
     * Get the output Message of the Operation.
     *
     * @return MessageInterface
     */
    public function getOutMessage()
    {
        // TODO: Implement getOutMessage() method.
    }

    /**
     * Get errors that the Operation may return.
     *
     * @return mixed[]
     */
    public function getErrors()
    {
        // TODO: Implement getErrors() method.
    }
}
