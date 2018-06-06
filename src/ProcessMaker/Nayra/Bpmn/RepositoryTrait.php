<?php

namespace ProcessMaker\Nayra\Bpmn;


use ProcessMaker\Nayra\Contracts\Repositories\RepositoryFactoryInterface;

/**
 * Repository trait.
 *
 * @package ProcessMaker\Nayra\Bpmn
 */
trait RepositoryTrait
{
    /**
     * Factory used to build this element.
     *
     * @var RepositoryFactoryInterface
     */
    private $factory;

    /**
     * RepositoryTrait constructor.
     *
     * @param RepositoryFactoryInterface $factory
     */
    public function __construct(RepositoryFactoryInterface $factory)
    {
        $this->factory = $factory;
    }

    /**
     * Get factory used to build this element.
     *
     * @return RepositoryFactoryInterface
     */
    public function getFactory()
    {
        return $this->factory;
    }

    /**
     * Set factory used to build this element.
     *
     * @param RepositoryFactoryInterface $factory
     *
     * @return $this
     */
    public function setFactory(RepositoryFactoryInterface $factory)
    {
        $this->factory = $factory;
        return $this;
    }
}
