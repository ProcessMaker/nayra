<?php

namespace ProcessMaker\Nayra\Engine;

use ProcessMaker\Nayra\Bpmn\Models\EventDefinitionBus;
use ProcessMaker\Nayra\Contracts\Bpmn\CatchEventInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\CollaborationInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\DataStoreInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\EventInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\ProcessInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\TransitionInterface;
use ProcessMaker\Nayra\Contracts\Engine\EngineInterface;
use ProcessMaker\Nayra\Contracts\Engine\EventDefinitionBusInterface;
use ProcessMaker\Nayra\Contracts\Engine\ExecutionInstanceInterface;
use ProcessMaker\Nayra\Contracts\Engine\JobManagerInterface;
use ProcessMaker\Nayra\Contracts\Repositories\StorageInterface;
use ProcessMaker\Nayra\Contracts\Storage\BpmnDocumentInterface;
use ProcessMaker\Nayra\Storage\BpmnDocument;

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

    protected $jobManager;

    /**
     * Actions to be executed after runToNextState is solved
     *
     * @var array
     */
    private $onNextState = [];

    /**
     * Event definition bus
     *
     * @var EventDefinitionBusInterface
     */
    protected $eventDefinitionBus;

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
        //If there are no pending transitions, next state callbacks are executed
        if (!$sum && $sum = count($this->onNextState)) {
            while ($action = array_shift($this->onNextState)) {
                $action();
            }
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
     * Defer the callback to be executed after the next state cycle.
     *
     * @param callable $callable
     *
     * @return EngineInterface
     */
    public function nextState(callable $callable)
    {
        $this->onNextState[] = $callable;
        return $this;
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
     * @param StorageInterface $storage
     *
     * @return ExecutionInstanceInterface|null
     */
    public function loadExecutionInstance($id, StorageInterface $storage)
    {
        // If exists return the already loaded instance by id 
        foreach($this->executionInstances as $executionInstance) {
            if ($executionInstance->getId() === $id) {
                return $executionInstance;
            }
        }
        // Create and load an instance by id
        $repository = $this->getRepository()->createExecutionInstanceRepository();
        $executionInstance = $repository->loadExecutionInstanceByUid($id, $storage);
        if (!$executionInstance) {
            return;
        }

        $this->loadProcess($executionInstance->getProcess());
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
        $process->setEngine($this);
        if (!in_array($process, $this->processes, true)) {
            $this->processes[] = $process;
            $this->registerCatchEvents($process);
        }
        return $this;
    }

    /**
     * Load definitions into BPMN Engine
     *
     * @param BpmnDocumentInterface $document
     *
     * @return EngineInterface
     */
    public function loadBpmnDocument(BpmnDocumentInterface $document)
    {
        $nodes = $document->getElementsByTagNameNS(BpmnDocument::BPMN_MODEL, 'collaboration');
        foreach ($nodes as $node) {
            $this->loadCollaboration($node->getBpmnElementInstance());
        }
        $processes = $document->getElementsByTagNameNS(BpmnDocument::BPMN_MODEL, 'process');
        foreach ($processes as $process) {
            $this->loadProcess($process->getBpmnElementInstance());
        }
        return $this;
    }

    /**
     * Load a collaboration
     *
     * @param CollaborationInterface $collaboration
     *
     * @return EngineInterface
     */
    public function loadCollaboration(CollaborationInterface $collaboration)
    {
        $this->getEventDefinitionBus()->setCollaboration($collaboration);
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
                $event->registerWithEngine($this);
            }
        }
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

    /**
     * Set a event definitions bus for the engine
     *
     * @param \ProcessMaker\Nayra\Contracts\Engine\EventDefinitionBusInterface $eventDefinitionBus
     *
     * @return EngineInterface
     */
    public function setEventDefinitionBus(EventDefinitionBusInterface $eventDefinitionBus)
    {
        $this->eventDefinitionBus = $eventDefinitionBus;
        return $this;
    }

    /**
     * Get the event definitions bus of the engine
     *
     * @return EventDefinitionBusInterface
     */
    public function getEventDefinitionBus()
    {
        $this->eventDefinitionBus = $this->eventDefinitionBus ?: new EventDefinitionBus;
        return $this->eventDefinitionBus;
    }
}
