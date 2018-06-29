<?php

namespace ProcessMaker\Test\Models;

use ProcessMaker\Nayra\Bpmn\Models\Token;
use ProcessMaker\Nayra\Bpmn\RepositoryTrait;
use ProcessMaker\Nayra\Contracts\Engine\ExecutionInstanceInterface;
use ProcessMaker\Nayra\Contracts\Repositories\ExecutionInstanceRepositoryInterface;
use ProcessMaker\Nayra\Contracts\Repositories\StorageInterface;
use ProcessMaker\Test\Models\ExecutionInstance;

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
        $instance = new ExecutionInstance();
        $process = $storage->getProcess($data['processId']);
        $dataStore = $storage->getFactory()->createDataStore();
        $dataStore->setData($data['data']);
        $instance->setProcess($process);
        $instance->setDataStore($dataStore);
        $process->getTransitions($storage->getFactory());

        //Load tokens:
        foreach($data['tokens'] as $tokenInfo) {
            $token = $this->createToken();
            $token->setProperties($tokenInfo);
            $element = $storage->getElementInstanceById($tokenInfo['elementId']);
            $element->addToken($instance, $token);
        }
        return $instance;
    }

    /**
     * Create or update an execution instance to a persistent storage.
     *
     * @param \ProcessMaker\Nayra\Contracts\Engine\ExecutionInstanceInterface $instance
     *
     * @return $this
     */
    public function storeExecutionInstance(ExecutionInstanceInterface $instance)
    {
        // TODO: Implement store() method.
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
     * Creates an instance of Token.
     *
     * @return \ProcessMaker\Nayra\Contracts\Bpmn\TokenInterface
     */
    public function createToken()
    {
        return new Token();
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
}
