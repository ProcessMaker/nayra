<?php

namespace ProcessMaker\Test\Models;

use ProcessMaker\Nayra\Bpmn\Models\Token;
use ProcessMaker\Nayra\Contracts\Bpmn\TokenInterface;
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

    /**
     * Persists instance's data related to the event Activity Activated
     *
     * @param $source
     * @param \ProcessMaker\Nayra\Contracts\Bpmn\TokenInterface $token
     *
     * @return mixed
     */
    public function persistActivityActivated($source, TokenInterface $token)
    {

    }

    /**
     * Persists instance's data related to the event Activity Exception
     *
     * @param $source
     * @param \ProcessMaker\Nayra\Contracts\Bpmn\TokenInterface $token
     *
     * @return mixed
     */
    public function persistActivityException($source, TokenInterface $token)
    {

    }

    /**
     * Persists instance's data related to the event Activity Completed
     *
     * @param $source
     * @param \ProcessMaker\Nayra\Contracts\Bpmn\TokenInterface $token
     *
     * @return mixed
     */
    public function persistActivityCompleted($source, TokenInterface $token)
    {

    }

    /**
     * Persists instance's data related to the event Activity Closed
     *
     * @param $source
     * @param \ProcessMaker\Nayra\Contracts\Bpmn\TokenInterface $token
     *
     * @return mixed
     */
    public function persistActivityClosed($source, TokenInterface $token)
    {

    }

    /**
     * Persists instance's data related to the event Process Instance Created
     *
     * @param $instance
     *
     * @return mixed
     */
    public function persistInstanceCreated($instance)
    {

    }
}
