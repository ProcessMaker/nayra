<?php

namespace ProcessMaker\Test\Models;

use ProcessMaker\Nayra\Bpmn\RepositoryTrait;
use ProcessMaker\Nayra\Contracts\Engine\ExecutionInstanceInterface;
use ProcessMaker\Nayra\Contracts\Repositories\ExecutionInstanceRepositoryInterface;
use ProcessMaker\Test\Models\ExecutionInstance;

/**
 * Execution Instance Repository.
 *
 * @package ProcessMaker\Models
 */
class ExecutionInstanceRepository implements ExecutionInstanceRepositoryInterface
{
    use RepositoryTrait;

    /**
     * Array to simulate a storage of execution instances.
     *
     * @var array $data
     */
    private static $data = [];

    /**
     * Create an execution instance.
     *
     * @return \ProcessMaker\Nayra\Contracts\Engine\ExecutionInstanceInterface
     */
    public function createExecutionInstance()
    {
        return new ExecutionInstance();
    }

    /**
     * Load an execution instance from a persistent storage.
     *
     * @param string $uid
     *
     * @return \ProcessMaker\Nayra\Contracts\Engine\ExecutionInstanceInterface
     */
    public function loadExecutionInstanceByUid($uid)
    {
        $data = self::$data[$uid];
        $instance = new \ProcessMaker\Test\Models\ExecutionInstance();
        $process = $this->getStorage()->getProcess($data['processId']);
        $dataStore = $this->getStorage()->getFactory()->createDataStore();
        $dataStore->setData($data['data']);
        $instance->setProcess($process);
        $instance->setDataStore($dataStore);
        $process->getTransitions($this->getStorage()->getFactory());

        //Load tokens:
        foreach($data['tokens'] as $tokenInfo) {
            $token = $this->getStorage()->getFactory()->createToken();
            $token->setProperties($tokenInfo);
            $element = $this->getStorage()->getElementInstanceById($tokenInfo['elementId']);
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
}
