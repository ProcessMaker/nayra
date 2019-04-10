<?php

namespace ProcessMaker\Nayra\Engine;

use ProcessMaker\Nayra\Contracts\Bpmn\CatchEventInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\DataStoreInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\EventInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\ProcessInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\TransitionInterface;
use ProcessMaker\Nayra\Contracts\Engine\ExecutionInstanceInterface;
use ProcessMaker\Nayra\Contracts\Engine\JobManagerInterface;
use ProcessMaker\Nayra\Contracts\Repositories\StorageInterface;

/**
 * Engine base behavior.
 *
 * @package ProcessMaker\Nayra\Bpmn
 */
trait EngineTrait
{
    /**
     * Instances of process.
     *
     * @var ExecutionInstance[]
     */
    private $executionInstances = [];

    /**
     * Loaded processes.
     *
     * @var ProcessInterface[]
     */
    private $processes = [];

    /**
     * Engine data store.
     *
     * @var DataStoreInterface $dataStore
     */
    private $dataStore;

    private $storage;

    protected $jobManager;

    /**
     * Execute all the process transitions.
     *
     * @return bool
     */
    public function step()
    {
        $sum = 0;
        //Execute transitions per instance
        foreach ($this->executionInstances as $executionInstance) {
            $sum += $executionInstance->getTransitions()->sum(function (TransitionInterface $transition) use ($executionInstance) {
                $result = $transition->execute($executionInstance) ? 1 : 0;
                return $result;
            }) > 0;
        }
        return $sum;
    }

    /**
     * Run to the next state.
     *
     * @param int $maxIterations
     *
     * @return bool
     */
    public function runToNextState($maxIterations = 0)
    {
        while ($this->step()) {
            $maxIterations--;
            if ($maxIterations === 0) {
                return false;
            }
        }
        return true;
    }

    /**
     * Create an execution instance of a process.
     *
     * @param ProcessInterface $process
     * @param DataStoreInterface $data
     * @param \ProcessMaker\Nayra\Contracts\Bpmn\EventInterface|null $event
     *
     * @return ExecutionInstanceInterface
     */
    public function createExecutionInstance(ProcessInterface $process, DataStoreInterface $data, EventInterface $event = null)
    {
        $this->loadProcess($process);

        $instanceRepo = $this->repository->createExecutionInstanceRepository();
        $executionInstance = $instanceRepo->createExecutionInstance($process, $data);

        $process->addInstance($executionInstance);
        $executionInstance->setProcess($process);
        $executionInstance->setDataStore($data);
        $executionInstance->linkToEngine($this);
        $this->executionInstances[] = $executionInstance;

        $instanceRepo->persistInstanceCreated($executionInstance);
        $process->notifyInstanceEvent(ProcessInterface::EVENT_PROCESS_INSTANCE_CREATED, $executionInstance, $event);
        return $executionInstance;
    }

    /**
     * Load an execution instance from the storage.
     *
     * @param string $id
     * @param DataStoreInterface $data
     *
     * @return ExecutionInstanceInterface|null
     */
    public function loadExecutionInstance($id)
    {
        $repository = $this->getRepository()->createExecutionInstanceRepository();
        $executionInstance = $repository->loadExecutionInstanceByUid($id, $this->getStorage());
        if (!$executionInstance) {
            return;
        }

        $executionInstance->linkToEngine($this);
        $executionInstance->getProcess()->addInstance($executionInstance);
        $this->executionInstances[] = $executionInstance;

        return $executionInstance;
    }

    /**
     * Close all the execution instances.
     *
     * @return bool
     */
    public function closeExecutionInstances()
    {
        $this->executionInstances = array_filter(
            $this->executionInstances,
            function (ExecutionInstanceInterface $executionInstance) {
                return !$executionInstance->close();
            }
        );
        return count($this->executionInstances) === 0;
    }

    /**
     * Get the engine data store used for global evaluations.
     *
     * @return DataStoreInterface
     */
    public function getDataStore()
    {
        return $this->dataStore;
    }

    /**
     * Set the engine data store used for global evaluations.
     *
     * @param DataStoreInterface $dataStore
     *
     * @return $this
     */
    public function setDataStore(DataStoreInterface $dataStore)
    {
        $this->dataStore = $dataStore;
        return $this;
    }

    /**
     * Load a process into the engine
     *
     * @param ProcessInterface $process
     *
     * @return $this
     */
    public function loadProcess(ProcessInterface $process)
    {
        if (!in_array($process, $this->processes)) {
            $this->processes[] = $process;
            $this->registerCatchEvents($process);
        }
        return $this;
    }

    /**
     * Register the catch events of the process.
     *
     * @param \ProcessMaker\Nayra\Contracts\Bpmn\ProcessInterface $process
     */
    private function registerCatchEvents(ProcessInterface $process)
    {
        foreach ($process->getEvents() as $event) {
            if ($event instanceof CatchEventInterface) {
                $event->registerCatchEvents($this);
            }
        }
    }

    /**
     * Get the repository storage of the engine.
     *
     * @return StorageInterface
     */
    public function getStorage()
    {
        return $this->storage;
    }

    /**
     * Set the repository storage of the engine.
     *
     * @param StorageInterface $storage
     */
    public function setStorage(StorageInterface $storage)
    {
        $this->storage = $storage;
    }

    /**
     * Get the engine job manager for timer tasks and events.
     *
     * @return JobManagerInterface
     */
    public function getJobManager()
    {
        return $this->jobManager;
    }

    /**
     * Set the engine job manager for timer tasks and events.
     *
     * @param JobManagerInterface|null $jobManager
     *
     * @return $this
     */
    public function setJobManager(JobManagerInterface $jobManager = null)
    {
        $this->jobManager = $jobManager;
        return $this;
    }
}
