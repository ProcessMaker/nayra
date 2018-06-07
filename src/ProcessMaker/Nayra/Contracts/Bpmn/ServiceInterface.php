<?php

namespace ProcessMaker\Nayra\Contracts\Bpmn;

/**
 * ServiceInterface (BPMN Interface) defines a set of operations that are implemented by Services.
 *
 * @package ProcessMaker\Nayra\Contracts\Bpmn
 */
interface ServiceInterface extends EntityInterface
{
    const BPMN_PROPERTY_IMPLEMENTATION_REF = 'implementationRef';
    const BPMN_PROPERTY_OPERATIONS = 'operations';
    const BPMN_PROPERTY_CALLABLE_ELEMENTS = 'callableElements';

    /**
     * Get the name of the element
     *
     * @return string
     */
    public function getName();

    /**
     * Set the name of the element
     *
     * @param string $name
     *
     * @return $this
     */
    public function setName($name);

    /**
     * Get the reference to a concrete artifact in the underlying implementation
     * representing the service.
     *
     * @return mixed
     */
    public function getImplementationRef();

    /**
     * Set the reference to a concrete artifact in the underlying implementation
     * representing the service.
     *
     * @param mixed $implementation
     *
     * @return $this
     */
    public function setImplementationRef($implementation);

    /**
     * Get the operations that are defined as part of the Service Interface
     *
     * @return OperationInterface[]
     */
    public function getOperations();

    /**
     * Set the operations that are defined as part of the Service Interface
     *
     * @param CollectionInterface $operations
     *
     * @return $this
     */
    public function setOperations(CollectionInterface $operations);
}
