<?php

namespace ProcessMaker\Nayra\Contracts\Repositories;

use ProcessMaker\Nayra\Contracts\Bpmn\ProcessInterface;
use ProcessMaker\Nayra\Contracts\Engine\EngineInterface;
use ProcessMaker\Nayra\Contracts\FactoryInterface;

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
     * @return FactoryInterface
     */
    public function getFactory();

    /**
     * Set factory used to build this element.
     *
     * @param FactoryInterface $factory
     *
     * @return $this
     */
    public function setFactory(FactoryInterface $factory);
}
