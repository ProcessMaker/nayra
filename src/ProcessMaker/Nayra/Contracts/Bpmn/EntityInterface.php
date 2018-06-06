<?php

namespace ProcessMaker\Nayra\Contracts\Bpmn;

use ProcessMaker\Nayra\Contracts\FactoryInterface;
use ProcessMaker\Nayra\Contracts\Repositories\StorageInterface;

/**
 * Entity could get and set properties for an bpmn element.
 *
 * @package ProcessMaker\Nayra\Contracts\Bpmn
 */
interface EntityInterface
{

    const BPMN_PROPERTY_ID = 'id';

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
     * @return \ProcessMaker\Nayra\Contracts\Repositories\StorageInterface
     */
    public function getFactory();

    /**
     * @param FactoryInterface|StorageInterface $factory
     * @return $this
     */
    public function setFactory(FactoryInterface $factory);

    /**
     * Get the name of the element.
     *
     * @return string
     */
    //public function getName();

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
     * @return mixed
     */
    public function setId($id);
}
