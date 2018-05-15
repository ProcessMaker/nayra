<?php

namespace ProcessMaker\Nayra\Engine;

use ProcessMaker\Nayra\Contracts\Bpmn\DataStoreInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\ProcessInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\TransitionInterface;
use ProcessMaker\Nayra\Contracts\Engine\ExecutionInstanceInterface;
use ProcessMaker\Nayra\Engine\ExecutionInstance;

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
     * Engine data store.
     *
     * @var DataStoreInterface $dataStore
     */
    private $dataStore;

    /**
     * Execute all the process transitions.
     *
     * @return bool
     */
    public function step()
    {
        $sum = 0;
        //Execute trnsitions per instance
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
     *
     * @return ExecutionInstanceInterface
     */
    public function createExecutionInstance(ProcessInterface $process, DataStoreInterface $data)
    {
        $executionInstance = new ExecutionInstance($this, $process, $data);
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
        $this->executionInstances = array_filter($this->executionInstances,
            function (ExecutionInstanceInterface $executionInstance) {
                return !$executionInstance->close();
            });
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
}
