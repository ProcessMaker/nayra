<?php

namespace ProcessMaker\Test\Models;

use ProcessMaker\Nayra\Contracts\Engine\ExecutionInstanceInterface;
use ProcessMaker\Nayra\Contracts\Repositories\ExecutionInstanceRepositoryInterface;
use ProcessMaker\Nayra\Contracts\Repositories\StorageInterface;

/**
 * Execution Instance Repository.
 *
 * @package ProcessMaker\Models
 */
class ExecutionInstanceRepository implements ExecutionInstanceRepositoryInterface
{
    /**
     * Array to simulate a storage of execution instances.
     *
     * @var array $data
     */
    private static $data = [];


    /**
     * Load an execution instance from a persistent storage.
     *
     * @param string $uid
     * @param StorageInterface $storage
     *
     * @return null|ExecutionInstanceInterface
     */
    public function loadExecutionInstanceByUid($uid, StorageInterface $storage)
    {
        if (empty(self::$data) || empty(self::$data[$uid])) {
            return;
        }
        $data = self::$data[$uid];
        $instance = $this->createExecutionInstance();
        $process = $storage->getProcess($data['processId']);
        $process->addInstance($instance);
        $dataStore = $storage->getFactory()->createDataStore();
        $dataStore->setData($data['data']);
        $instance->setProcess($process);
        $instance->setDataStore($dataStore);
        $process->getTransitions($storage->getFactory());

        //Load tokens:
        foreach($data['tokens'] as $tokenInfo) {
            $token = $storage->getFactory()->getTokenRepository()->createTokenInstance();
            $token->setProperties($tokenInfo);
            $element = $storage->getElementInstanceById($tokenInfo['elementId']);
            $element->addToken($instance, $token);
        }
        return $instance;
    }

    /**
     * Set the test data to be loaded.
     *
     * @param array $data
     */
    public function setRawData(array $data)
    {
        self::$data = $data;
    }

    /**
     * Creates an execution instance.
     *
     * @return \ProcessMaker\Test\Models\ExecutionInstance
     */
    public function createExecutionInstance()
    {
        return new ExecutionInstance();
    }

    /**
     * Persists instance's data related to the event Process Instance Created
     *
     * @param ExecutionInstanceInterface $instance
     *
     * @return mixed
     */
    public function persistInstanceCreated(ExecutionInstanceInterface $instance)
    {

    }

    /**
     * Persists instance's data related to the event Process Instance Completed
     *
     * @param ExecutionInstanceInterface $instance
     *
     * @return mixed
     */
    public function persistInstanceCompleted(ExecutionInstanceInterface $instance)
    {
    }
}
