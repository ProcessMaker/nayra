<?php

namespace ProcessMaker\Bpmn;

use ProcessMaker\Nayra\Contracts\Engine\EngineInterface;
use ProcessMaker\Nayra\Contracts\Engine\EventDefinitionBusInterface;
use ProcessMaker\Nayra\Contracts\Engine\JobManagerInterface;
use ProcessMaker\Nayra\Contracts\EventBusInterface;
use ProcessMaker\Nayra\Contracts\RepositoryInterface;
use ProcessMaker\Nayra\Engine\EngineTrait;

/**
 * Test implementation for EngineInterface.
 */
class TestEngine implements EngineInterface
{
    use EngineTrait;

    /**
     * @var RepositoryInterface
     */
    private $repository;

    /**
     * @var EventBusInterface
     */
    protected $dispatcher;

    /**
     * Test engine constructor.
     *
     * @param \ProcessMaker\Nayra\Contracts\RepositoryInterface $repository
     * @param \ProcessMaker\Nayra\Contracts\EventBusInterface $dispatcher
     * @param \ProcessMaker\Nayra\Contracts\Engine\JobManagerInterface|null $jobManager
     * @param \ProcessMaker\Nayra\Contracts\Engine\EventDefinitionBusInterface|null $eventDefinitionBus
     */
    public function __construct(RepositoryInterface $repository, EventBusInterface $dispatcher, JobManagerInterface $jobManager = null, EventDefinitionBusInterface $eventDefinitionBus = null)
    {
        $this->setRepository($repository);
        $this->setDispatcher($dispatcher);
        $this->setJobManager($jobManager);
        $this->setDataStore($repository->createDataStore());
        $eventDefinitionBus ? $this->setEventDefinitionBus($eventDefinitionBus) : null;
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
