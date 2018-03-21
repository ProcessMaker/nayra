<?php

namespace ProcessMaker\Bpmn;

use ProcessMaker\Nayra\Contracts\Bpmn\TransitionInterface;
use ProcessMaker\Nayra\Contracts\Engine\EngineInterface;
use ProcessMaker\Nayra\Engine\EngineTrait;
use ProcessMaker\Models\RepositoryFactory;
use ProcessMaker\Nayra\Contracts\Repositories\RepositoryFactoryInterface;
use Illuminate\Contracts\Events\Dispatcher;

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
     * @var \Illuminate\Contracts\Events\Dispatcher $dispatcher
     */
    protected $dispatcher;

    public function __construct(RepositoryFactoryInterface $repository, Dispatcher $dispatcher)
    {
        $this->repositoryFactory = new RepositoryFactory();
        $this->dispatcher = $dispatcher;
    }

    /**
     * @return \Illuminate\Contracts\Events\Dispatcher
     */
    public function getDispatcher()
    {
        return $this->dispatcher;
    }

    /**
     * @param \Illuminate\Contracts\Events\Dispatcher $dispatcher
     *
     * @return $this
     */
    public function setDispatcher(\Illuminate\Contracts\Events\Dispatcher $dispatcher)
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