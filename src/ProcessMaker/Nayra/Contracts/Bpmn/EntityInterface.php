<?php

namespace ProcessMaker\Nayra\Contracts\Bpmn;

use ProcessMaker\Nayra\Contracts\Repositories\RepositoryFactoryInterface;

/**
 * Entity could get and set properties for an bpmn element.
 *
 * @package ProcessMaker\Nayra\Contracts\Bpmn
 */
interface EntityInterface
{

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
     * @param $name
     * @param $value
     *
     * @return $this
     */
    public function setProperty($name, $value);

    /**
     *
     * @param $name
     * @param mixed $default
     *
     * @return mixed
     */
    public function getProperty($name, $default=null);

    /**
     * @return \ProcessMaker\Nayra\Contracts\Repositories\RepositoryFactoryInterface
     */
    public function getFactory();

    /**
     * @param RepositoryFactoryInterface $factory
     * @return $this
     */
    public function setFactory(RepositoryFactoryInterface $factory);

    /**
     * Get the name of the element.
     *
     * @return string
     */
    //public function getName();

    /**
     *
     *
     * @return int
     */
    //public function getId();

}
