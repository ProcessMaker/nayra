<?php

namespace ProcessMaker\Nayra\Engine;

use ProcessMaker\Nayra\Bpmn\Collection;
use ProcessMaker\Nayra\Contracts\Bpmn\DataStoreInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\ProcessInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\TokenInterface;
use ProcessMaker\Nayra\Contracts\Engine\EngineInterface;
use ProcessMaker\Nayra\Contracts\Engine\ExecutionInstanceInterface;

/**
 * Execution instance for the engine.
 *
 * @package ProcessMaker\Nayra\Bpmn
 */
class ExecutionInstance implements ExecutionInstanceInterface
{
    /**
     * Process executed.
     *
     * @var ProcessInterface
     */
    private $process;

    /**
     * Data used for the process.
     *
     * @var DataStoreInterface
     */
    private $dataStore;

    /**
     * Transitions to be executed.
     *
     * @var \ProcessMaker\Nayra\Contracts\Bpmn\TransitionInterface[]
     */
    private $transitions;

    /**
     * @var \ProcessMaker\Nayra\Contracts\Bpmn\TokenInterface[]
     */
    private $tokens;

    /**
     * ExecutionInstance constructor.
     *
     * @param EngineInterface $engine
     * @param ProcessInterface $process
     * @param DataStoreInterface $data
     */
    public function __construct(EngineInterface $engine, ProcessInterface $process, DataStoreInterface $data)
    {
        $process->setDispatcher($engine->getDispatcher());
        $process->addInstance($this);
        $this->process = $process;
        $this->dataStore = $data;
        $this->transitions = $process->getTransitions($engine->getRepositoryFactory());
        $this->tokens = new Collection;
    }

    /**
     * Get the process executed.
     *
     * @return ProcessInterface
     */
    public function getProcess()
    {
        return $this->process;
    }

    /**
     * Get the data context.
     *
     * @return DataStoreInterface
     */
    public function getDataStore()
    {
        return $this->dataStore;
    }

    /**
     * Get transitions.
     *
     * @return \ProcessMaker\Nayra\Contracts\Bpmn\TransitionInterface[]
     */
    public function getTransitions()
    {
        return $this->transitions;
    }

    /**
     * Close the execution instance.
     *
     * @return bool
     */
    public function close()
    {
        return true;
    }

    /**
     * Add a token to the current instance.
     *
     * @param \ProcessMaker\Nayra\Contracts\Bpmn\TokenInterface $token
     *
     * @return $this
     */
    public function addToken(TokenInterface $token)
    {
        $this->tokens->push($token);
        return $this;
    }

    /**
     * Remove a token from the current instance.
     *
     * @param \ProcessMaker\Nayra\Contracts\Bpmn\TokenInterface $token
     *
     * @return $this
     */
    public function removeToken(TokenInterface $token)
    {
        $tokenIndex = $this->tokens->indexOf($token);
        $this->tokens->splice($tokenIndex, 1);
        return $this;
    }

    /**
     * Get all tokens from the current instance.
     *
     * @return \ProcessMaker\Nayra\Contracts\Bpmn\CollectionInterface
     */
    public function getTokens()
    {
        return $this->tokens;
    }
}