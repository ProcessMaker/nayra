<?php

namespace ProcessMaker\Nayra\Bpmn;


use ProcessMaker\Nayra\Contracts\FactoryInterface;
use ProcessMaker\Nayra\Contracts\Repositories\StorageInterface;

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
     * @var StorageInterface
     */
    private $factory;

    /**
     * RepositoryTrait constructor.
     *
     * @param StorageInterface $factory
     */
    public function __construct(StorageInterface $factory)
    {
        $this->factory = $factory;
    }

    /**
     * Get factory used to build this element.
     *
     * @return StorageInterface
     */
    public function getStorage()
    {
        return $this->factory;
    }
}
