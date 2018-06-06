<?php

namespace ProcessMaker\Models;

use ProcessMaker\Nayra\Bpmn\Lane;
use ProcessMaker\Nayra\Bpmn\LaneSet;
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
        $process->setFactory($this->getStorage());
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
        return $this->getStorage()->getProcess($uid);
    }

    /**
     * Create or update a process to a persistent storage.
     *
     * @param \ProcessMaker\Nayra\Contracts\Bpmn\ProcessInterface $process
     * @param bool $saveChildElements
     *
     * @return $this
     */
    public function store(ProcessInterface $process, $saveChildElements = false)
    {

    }

    /**
     * Create a lane set instance.
     *
     * @return \ProcessMaker\Nayra\Contracts\Bpmn\LaneSetInterface
     */
    public function createLaneSetInstance()
    {
        $laneSet = new LaneSet;
        $laneSet->setFactory($this->getStorage());
        return $laneSet;
    }

    /**
     * Create a lane instance.
     *
     * @return \ProcessMaker\Nayra\Contracts\Bpmn\LaneInterface
     */
    public function createLaneInstance()
    {
        $lane = new Lane;
        $lane->setFactory($this->getStorage());
        return $lane;
    }
}
