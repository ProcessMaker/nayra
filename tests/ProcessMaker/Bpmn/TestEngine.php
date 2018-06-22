<?php

namespace ProcessMaker\Bpmn;

use ProcessMaker\Nayra\Contracts\Engine\EngineInterface;
use ProcessMaker\Nayra\Contracts\EventBusInterface;
use ProcessMaker\Nayra\Contracts\RepositoryInterface;
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
     * @var RepositoryInterface
     */
    private $repository;

    /**
     * @var EventBusInterface $dispatcher
     */
    protected $dispatcher;

    /**
     * Test engine constructor.
     *
     * @param RepositoryInterface $repository
     * @param EventBusInterface $dispatcher
     */
    public function __construct(RepositoryInterface $repository, EventBusInterface $dispatcher)
    {
        $this->repository = $repository;
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
     * @return FactoryInterface
     */
    public function getRepository()
    {
        return $this->repository;
    }

    /**
     * @param RepositoryInterface $repository
     *
     * @return $this
     */
    public function setRepository(RepositoryInterface $repository)
    {
        $this->repository = $repository;
        return $this;
    }
}
