<?php

namespace ProcessMaker\Nayra\Contracts\Bpmn;

use ProcessMaker\Nayra\Contracts\Repositories\StorageInterface;
use ProcessMaker\Nayra\Contracts\RepositoryInterface;
use ProcessMaker\Nayra\Contracts\Storage\BpmnElementInterface;

/**
 * Entity could get and set properties for an bpmn element.
 *
 * @package ProcessMaker\Nayra\Contracts\Bpmn
 */
interface EntityInterface
{
    const BPMN_PROPERTY_ID = 'id';
    const BPMN_PROPERTY_NAME = 'name';

    /**
     *
     * @param array $properties
     */
    public function setProperties(array $properties);

    /**
     *
     * @return array
     */
    public function getProperties();

    /**
     *
     * @param string $name
     * @param mixed $value
     *
     * @return $this
     */
    public function setProperty($name, $value);

    /**
     *
     * @param string $name
     * @param mixed $default
     *
     * @return mixed
     */
    public function getProperty($name, $default = null);

    /**
     * Add value to collection property.
     *
     * @param string $name
     * @param mixed $value
     *
     * @return $this
     */
    public function addProperty($name, $value);

    /**
     * @return \ProcessMaker\Nayra\Contracts\RepositoryInterface
     */
    public function getRepository();

    /**
     * @param RepositoryInterface $factory
     *
     * @return $this
     */
    public function setRepository(RepositoryInterface $factory);

    /**
     * Get Entity ID
     *
     * @return mixed
     */
    public function getId();

    /**
     * Set Entity ID
     *
     * @param string $id
     *
     * @return $this
     */
    public function setId($id);

    /**
     * Get the name of the element.
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
     * @return \ProcessMaker\Nayra\Contracts\Repositories\StorageInterface
     */
    public function getOwnerDocument();

    /**
     * Get DOM element of this object.
     *
     * @param StorageInterface $ownerDocument
     *
     * @return $this
     */
    public function setOwnerDocument(StorageInterface $ownerDocument);

    /**
     * @return \ProcessMaker\Nayra\Contracts\Storage\BpmnElementInterface
     */
    public function getBpmnElement();

    /**
     * Set DOM element of this object.
     *
     * @param BpmnElementInterface $domElement
     *
     * @return $this
     */
    public function setBpmnElement(BpmnElementInterface $ownerDocument);
}
