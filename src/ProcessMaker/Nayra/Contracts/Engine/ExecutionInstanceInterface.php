<?php

namespace ProcessMaker\Nayra\Contracts\Engine;

use ProcessMaker\Nayra\Contracts\Bpmn\DataStoreInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\ProcessInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\TokenInterface;

/**
 * Execution instance for the engine.
 *
 * @package ProcessMaker\Nayra\Bpmn
 */
interface ExecutionInstanceInterface
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
}