<?php

namespace ProcessMaker\Nayra\Contracts\Repositories;

use ProcessMaker\Nayra\Contracts\Bpmn\ProcessInterface;

/**
 * Repository for ProcessInterface
 *
 * @package ProcessMaker\Nayra\Contracts\Repositories
 */
interface ProcessRepositoryInterface extends RepositoryInterface
{

    /**
     * Create a process instance.
     *
     * @return \ProcessMaker\Nayra\Contracts\Bpmn\ProcessInterface
     */
    public function createProcessInstance();

    /**
     * Load a process from a persistent storage.
     *
     * @param string $uid
     *
     * @return \ProcessMaker\Nayra\Contracts\Bpmn\ProcessInterface
     */
    public function loadProcessByUid($uid);

    /**
     * Create or update a process to a persistent storage.
     *
     * @param \ProcessMaker\Nayra\Contracts\Bpmn\ProcessInterface $process
     * @param $saveChildElements
     *
     * @return $this
     */
    public function store(ProcessInterface $process, $saveChildElements=false);
}
