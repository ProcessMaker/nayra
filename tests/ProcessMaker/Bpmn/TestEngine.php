<?php

namespace ProcessMaker\Bpmn;

use ProcessMaker\Nayra\Contracts\Engine\EngineInterface;
use ProcessMaker\Nayra\Contracts\EventBusInterface;
use ProcessMaker\Nayra\Contracts\FactoryInterface;
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
    private $factory;

    /**
     * @var EventBusInterface $dispatcher
     */
    protected $dispatcher;

    public function __construct(FactoryInterface $factory, EventBusInterface $dispatcher)
    {
        $this->factory = $factory;
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
    public function getFactory()
    {
        return $this->factory;
    }

    /**
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