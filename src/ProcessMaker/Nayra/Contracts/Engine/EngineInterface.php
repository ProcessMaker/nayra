<?php

namespace ProcessMaker\Nayra\Contracts\Engine;

use ProcessMaker\Nayra\Contracts\Bpmn\CollaborationInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\DataStoreInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\EventInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\ProcessInterface;
use ProcessMaker\Nayra\Contracts\EventBusInterface;
use ProcessMaker\Nayra\Contracts\Repositories\StorageInterface;
use ProcessMaker\Nayra\Contracts\RepositoryInterface;
use ProcessMaker\Nayra\Contracts\Storage\BpmnDocumentInterface;

/**
 * Engine interface.
 *
 * @package ProcessMaker\Nayra\Contracts\Engine
 */
interface EngineInterface
{
    /**
     * Factory used to create the concrete bpmn classes for the engine.
     *
     * @param RepositoryInterface $factory
     *
     * @return $this
     */
    public function setRepository(RepositoryInterface $factory);

    /**
     * @return RepositoryInterface
     */
    public function getRepository();

    /**
     * Dispatcher of events used by the engine.
     *
     * @param EventBusInterface $dispatcher
     *
     * @return $this
     */
    public function setDispatcher(EventBusInterface $dispatcher);

    /**
     * @return EventBusInterface
     */
    public function getDispatcher();

    /**
     * Run to the next state.
     *
     * @param int $maxIterations
     *
     * @return bool
     */
    public function runToNextState($maxIterations = 0);

    /**
     * Execute all the active transitions.
     *
     * @return bool
     */
    public function step();

    /**
     * Load a process into the engine
     *
     * @param ProcessInterface $process
     *
     * @return $this
     */
    public function loadProcess(ProcessInterface $process);

    /**
     * Create an execution instance of a process.
     *
     * @param ProcessInterface $process
     * @param DataStoreInterface $data
     * @param \ProcessMaker\Nayra\Contracts\Bpmn\EventInterface|null $event
     *
     * @return ExecutionInstanceInterface
     */
    public function createExecutionInstance(ProcessInterface $process, DataStoreInterface $data, EventInterface $event = null);

    /**
     * Load an execution instance from the storage.
     *
     * @param string $id
     * @param DataStoreInterface $data
     *
     * @return ExecutionInstanceInterface
     */
    public function loadExecutionInstance($id);

    /**
     * Close all the execution instances.
     *
     * @return bool
     */
    public function closeExecutionInstances();

    /**
     * Get the engine data store used for global evaluations.
     *
     * @return DataStoreInterface
     */
    public function getDataStore();

    /**
     * Set the engine data store used for global evaluations.
     *
     * @param DataStoreInterface $dataStore
     *
     * @return $this
     */
    public function setDataStore(DataStoreInterface $dataStore);

    /**
     * Set the repository storage of the engine.
     *
     * @param StorageInterface $repository
     */
    public function setStorage(StorageInterface $repository);

    /**
     * Get the engine job manager for timer tasks and events.
     *
     * @return JobManagerInterface
     */
    public function getJobManager();

    /**
     * Set the engine job manager for timer tasks and events.
     *
     * @param JobManagerInterface|null $jobManager
     *
     * @return $this
     */
    public function setJobManager(JobManagerInterface $jobManager = null);

    /**
     * Get the event definitions bus of the engine
     *
     * @return EventDefinitionBusInterface
     */
    public function getEventDefinitionBus();

    /**
     * Set a event definitions bus for the engine
     *
     * @param \ProcessMaker\Nayra\Contracts\Engine\EventDefinitionBusInterface $eventDefinitionBus
     *
     * @return EngineInterface
     */
    public function setEventDefinitionBus(EventDefinitionBusInterface $eventDefinitionBus);

    /**
     * Load definitions into BPMN Engine
     *
     * @param BpmnDocumentInterface $document
     *
     * @return EngineInterface
     */
    public function loadBpmnDocument(BpmnDocumentInterface $document);

    /**
     * Load a collaboration
     *
     * @param CollaborationInterface $collaboration
     *
     * @return EngineInterface
     */
    public function loadCollaboration(CollaborationInterface $collaboration);

    /**
     * Get the repository storage of the engine.
     *
     * @return StorageInterface
     */
    public function getStorage();
}
