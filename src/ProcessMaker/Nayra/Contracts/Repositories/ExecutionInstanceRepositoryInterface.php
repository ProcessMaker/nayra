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
     * Creates an instance of Token.
     *
     * @return \ProcessMaker\Nayra\Contracts\Bpmn\TokenInterface
     */
    public function createToken();

    /**
     * Creates an execution instance.
     *
     * @return \ProcessMaker\Test\Models\ExecutionInstance
     */
    public function createExecutionInstance();

    /**
     * Persists instance's data related to the event Activity Activated
     *
     * @param $source
     * @param \ProcessMaker\Nayra\Contracts\Bpmn\TokenInterface $token
     *
     * @return mixed
     */
    public function persistActivityActivated($source, TokenInterface $token);

    /**
     * Persists instance's data related to the event Activity Exception
     *
     * @param $source
     * @param \ProcessMaker\Nayra\Contracts\Bpmn\TokenInterface $token
     *
     * @return mixed
     */
    public function persistActivityException($source, TokenInterface $token);

    /**
     * Persists instance's data related to the event Activity Completed
     *
     * @param $source
     * @param \ProcessMaker\Nayra\Contracts\Bpmn\TokenInterface $token
     *
     * @return mixed
     */
    public function persistActivityCompleted($source, TokenInterface $token);

    /**
     * Persists instance's data related to the event Activity Closed
     *
     * @param $source
     * @param \ProcessMaker\Nayra\Contracts\Bpmn\TokenInterface $token
     *
     * @return mixed
     */
    public function persistActivityClosed($source, TokenInterface $token);

    /**
     * Persists instance's data related to the event Process Instance Created
     *
     * @param $instance
     *
     * @return mixed
     */
    public function persistInstanceCreated($instance);
}
