<?php

namespace ProcessMaker\Nayra\Contracts\Engine;

use ProcessMaker\Nayra\Contracts\Bpmn\DataStoreInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\EntityInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\ProcessInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\TokenInterface;

/**
 * Execution instance for the engine.
 */
interface ExecutionInstanceInterface extends EntityInterface
{
    /**
     * Get the process executed.
     *
     * @return ProcessInterface
     */
    public function getProcess();

    /**
     * Get the data context.
     *
     * @return DataStoreInterface
     */
    public function getDataStore();

    /**
     * Get transitions.
     *
     * @return \ProcessMaker\Nayra\Contracts\Bpmn\TransitionInterface[]
     */
    public function getTransitions();

    /**
     * Close the execution instance.
     *
     * @return bool
     */
    public function close();

    /**
     * Add a token to the current instance.
     *
     * @param \ProcessMaker\Nayra\Contracts\Bpmn\TokenInterface $token
     *
     * @return $this
     */
    public function addToken(TokenInterface $token);

    /**
     * Remove a token to the current instance.
     *
     * @param \ProcessMaker\Nayra\Contracts\Bpmn\TokenInterface $token
     *
     * @return $this
     */
    public function removeToken(TokenInterface $token);

    /**
     * Get all tokens from the current instance.
     *
     * @return \ProcessMaker\Nayra\Contracts\Bpmn\CollectionInterface
     */
    public function getTokens();

    /**
     * Link the instance to a engine, process and data store.
     *
     * @param EngineInterface $engine
     * @param ProcessInterface $process
     * @param DataStoreInterface $data
     */
    public function linkTo(EngineInterface $engine, ProcessInterface $process, DataStoreInterface $data);

    /**
     * Set the process executed.
     *
     * @param \ProcessMaker\Nayra\Contracts\Bpmn\ProcessInterface $process
     *
     * @return $this
     */
    public function setProcess(ProcessInterface $process);

    /**
     * Set the data context.
     *
     * @param \ProcessMaker\Nayra\Contracts\Bpmn\DataStoreInterface $dataStore
     *
     * @return $this
     */
    public function setDataStore(DataStoreInterface $dataStore);

    /**
     * Link the instance to an engine.
     *
     * @param \ProcessMaker\Nayra\Contracts\Engine\EngineInterface $engine
     *
     * @return $this
     */
    public function linkToEngine(EngineInterface $engine);
}
