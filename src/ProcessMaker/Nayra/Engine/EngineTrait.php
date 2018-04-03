<?php

namespace ProcessMaker\Nayra\Engine;

use ProcessMaker\Nayra\Contracts\Bpmn\DataStoreInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\ProcessInterface;
use ProcessMaker\Nayra\Bpmn\BaseTrait;
use ProcessMaker\Nayra\Bpmn\Collection;
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
     * Execute all the active transitions.
     *
     * @return bool
     */
    public function step()
    {
        $sum = 0;
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
     * @return int
     */
    public function createExecutionInstance(ProcessInterface $process, DataStoreInterface $data)
    {
        $executionInstance = new ExecutionInstance($this, $process, $data);
        $this->executionInstances[] = $executionInstance;
        return count($this->executionInstances) - 1;
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
}
