<?php

namespace ProcessMaker\Nayra\Engine;

use ProcessMaker\Nayra\Bpmn\BaseTrait;
use ProcessMaker\Nayra\Bpmn\Collection;
use ProcessMaker\Nayra\Contracts\Bpmn\ActivityInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\DataStoreInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\ProcessInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\TokenInterface;
use ProcessMaker\Nayra\Contracts\Engine\EngineInterface;

/**
 * Execution instance for the engine.
 */
trait ExecutionInstanceTrait
{
    use BaseTrait;

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
     * @var \ProcessMaker\Nayra\Contracts\Bpmn\TokenInterface[]|\ProcessMaker\Nayra\Contracts\Bpmn\CollectionInterface
     */
    private $tokens;

    public $uniqid;

    /**
     * ExecutionInstance constructor.
     *
     * @param EngineInterface $engine
     * @param ProcessInterface $process
     * @param DataStoreInterface $data
     */
    protected function initExecutionInstance()
    {
        $this->uniqid = \uniqid('', true);
        $this->tokens = new Collection;
    }

    /**
     * Link the instance to a engine, process and data store.
     *
     * @param EngineInterface $engine
     * @param ProcessInterface $process
     * @param DataStoreInterface $data
     */
    public function linkTo(EngineInterface $engine, ProcessInterface $process, DataStoreInterface $data)
    {
        $process->setDispatcher($engine->getDispatcher());
        $process->addInstance($this);
        $this->process = $process;
        $this->dataStore = $data;
        $this->transitions = $process->getTransitions($engine->getRepository());
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
     * Set the process executed.
     *
     * @param \ProcessMaker\Nayra\Contracts\Bpmn\ProcessInterface $process
     *
     * @return $this
     */
    public function setProcess(ProcessInterface $process)
    {
        $this->process = $process;

        return $this;
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
     * Set the data context.
     *
     * @param \ProcessMaker\Nayra\Contracts\Bpmn\DataStoreInterface $dataStore
     *
     * @return $this
     */
    public function setDataStore(DataStoreInterface $dataStore)
    {
        $this->dataStore = $dataStore;

        return $this;
    }

    /**
     * Get transitions.
     *
     * @return \ProcessMaker\Nayra\Contracts\Bpmn\TransitionInterface[]|\ProcessMaker\Nayra\Contracts\Bpmn\CollectionInterface
     */
    public function getTransitions()
    {
        return $this->transitions;
    }

    /**
     * Link the instance to an engine.
     *
     * @param \ProcessMaker\Nayra\Contracts\Engine\EngineInterface $engine
     *
     * @return $this
     */
    public function linkToEngine(EngineInterface $engine)
    {
        $this->getProcess()->setDispatcher($engine->getDispatcher());
        $this->transitions = $this->getProcess()->getTransitions($engine->getRepository());

        return $this;
    }

    /**
     * Close the execution instance.
     *
     * @return bool
     */
    public function close()
    {
        $tokens = $this->getTokens()->toArray();
        foreach ($tokens as $token) {
            $token->setStatus(ActivityInterface::TOKEN_STATE_CLOSED);
        }

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
