<?php

namespace ProcessMaker\Nayra\Contracts\Repositories;

use ProcessMaker\Nayra\Bpmn\Models\Token;
use ProcessMaker\Nayra\Contracts\Bpmn\TokenInterface;
use ProcessMaker\Nayra\Contracts\Engine\ExecutionInstanceInterface;

/**
 * Repository for ExecutionInstanceInterface
 *
 */
interface ExecutionInstanceRepositoryInterface
{

    /**
     * Load an execution instance from a persistent storage.
     *
     * @param string $uid
     * @param StorageInterface $storage
     *
     * @return \ProcessMaker\Nayra\Contracts\Engine\ExecutionInstanceInterface
     */
    public function loadExecutionInstanceByUid($uid, StorageInterface $storage);

    /**
     * Creates an execution instance.
     *
     * @return \ProcessMaker\Test\Models\ExecutionInstance
     */
    public function createExecutionInstance();

    /**
     * Persists instance's data related to the event Process Instance Created
     *
     * @param ExecutionInstanceInterface $instance
     *
     * @return mixed
     */
    public function persistInstanceCreated(ExecutionInstanceInterface $instance);

    /**
     * Persists instance's data related to the event Process Instance Completed
     *
     * @param ExecutionInstanceInterface $instance
     *
     * @return mixed
     */
    public function persistInstanceCompleted(ExecutionInstanceInterface $instance);
}
