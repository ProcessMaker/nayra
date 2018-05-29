<?php

namespace ProcessMaker\Nayra\Contracts\Repositories;

use ProcessMaker\Nayra\Contracts\Bpmn\ProcessInterface;
use ProcessMaker\Nayra\Contracts\Engine\EngineInterface;

/**
 * Repository Interface.
 *
 * @package ProcessMaker\Nayra\Contracts\Repositories
 */
interface RepositoryInterface
{
    /**
     * Get factory used to build this element.
     *
     * @return RepositoryFactoryInterface
     */
    public function getFactory();

    /**
     * Set factory used to build this element.
     *
     * @param RepositoryFactoryInterface $factory
     *
     * @return $this
     */
    public function setFactory(RepositoryFactoryInterface $factory);
}
