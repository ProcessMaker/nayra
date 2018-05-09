<?php

namespace ProcessMaker\Bpmn;

use ProcessMaker\Models\RepositoryFactory;
use ProcessMaker\Nayra\Contracts\Engine\EngineInterface;
use ProcessMaker\Nayra\Contracts\EventBusInterface;
use ProcessMaker\Nayra\Contracts\Repositories\RepositoryFactoryInterface;
use ProcessMaker\Nayra\Engine\EngineTrait;

/**
 * Test implementation for EngineInterface.
 *
 * @package ProcessMaker\Bpmn
 */
class TestEngine implements EngineInterface
{
    use EngineTrait;

    /**
     * @var RepositoryFactoryInterface
     */
    private $repositoryFactory;

    /**
     * @var EventBusInterface $dispatcher
     */
    protected $dispatcher;

    public function __construct(RepositoryFactoryInterface $repository, EventBusInterface $dispatcher)
    {
        $this->repositoryFactory = new RepositoryFactory();
        $this->dispatcher = $dispatcher;
    }

    /**
     * @return EventBusInterface
     */
    public function getDispatcher()
    {
        return $this->dispatcher;
    }

    /**
     * @param EventBusInterface $dispatcher
     *
     * @return $this
     */
    public function setDispatcher(EventBusInterface $dispatcher)
    {
        $this->dispatcher = $dispatcher;
        return $this;
    }

    /**
     * @return RepositoryFactoryInterface
     */
    public function getRepositoryFactory()
    {
        return $this->repositoryFactory;
    }

    /**
     * @param RepositoryFactoryInterface $repositoryFactory
     *
     * @return $this
     */
    public function setRepositoryFactory(RepositoryFactoryInterface $repositoryFactory)
    {
        $this->repositoryFactory = $repositoryFactory;
        return $this;
    }
}