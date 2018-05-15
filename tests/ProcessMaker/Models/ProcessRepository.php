<?php

namespace ProcessMaker\Models;

use ProcessMaker\Nayra\Bpmn\RepositoryTrait;
use ProcessMaker\Nayra\Contracts\Bpmn\ProcessInterface;
use ProcessMaker\Nayra\Contracts\Repositories\ProcessRepositoryInterface;

/**
 * Process Repository
 *
 * @package ProcessMaker\Models
 */
class ProcessRepository implements ProcessRepositoryInterface
{

    use RepositoryTrait;

    /**
     * Create a process.
     *
     * @return Process
     */
    public function createProcessInstance()
    {
        $process = new Process;
        $process->setFactory($this->getFactory());
        return $process;
    }

    /**
     * Create a process.
     *
     * @return Process
     */
    public function createCustomProcessInstance()
    {
        return new CustomProcess;
    }

    /**
     * Load a process from storage.
     *
     * @param string $uid
     *
     * @return Process
     */
    public function loadProcessByUid($uid)
    {

    }

    /**
     * Create or update a process to a persistent storage.
     *
     * @param \ProcessMaker\Nayra\Contracts\Bpmn\ProcessInterface $process
     * @param $saveChildElements
     *
     * @return $this
     */
    public function store(ProcessInterface $process, $saveChildElements = false)
    {

    }

    /**
     * Create an instance of the entity.
     *
     * @param ProcessInterface|null $process
     *
     * @return \ProcessMaker\Nayra\Contracts\Bpmn\EntityInterface
     */
    public function create(ProcessInterface $process = null)
    {
        return new Process;
    }
}
