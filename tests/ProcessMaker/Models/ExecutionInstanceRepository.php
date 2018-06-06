<?php

namespace ProcessMaker\Models;

use ProcessMaker\Models\ExecutionInstance;
use ProcessMaker\Nayra\Bpmn\RepositoryTrait;
use ProcessMaker\Nayra\Contracts\Bpmn\DataStoreInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\TokenInterface;
use ProcessMaker\Nayra\Contracts\Engine\ExecutionInstanceInterface;
use ProcessMaker\Nayra\Contracts\Repositories\ExecutionInstanceRepositoryInterface;
use ProcessMaker\Nayra\Contracts\Repositories\ProcessRepositoryInterface;
use ProcessMaker\Nayra\Contracts\Repositories\TokenRepositoryInterface;

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
        $instance = new ExecutionInstance();
        $processRepository = $this->getStorage()->getFactory()->createInstanceOf(ProcessRepositoryInterface::class, $this->getStorage());
        $process = $processRepository->loadProcessByUid($data['processId']);
        $dataStore = $this->getStorage()->getFactory()->createInstanceOf(DataStoreInterface::class);
        $dataStore->setData($data['data']);
        $instance->setProcess($process);
        $instance->setDataStore($dataStore);
        $process->getTransitions($this->getStorage()->getFactory());

        //Load tokens:
        $tokenRepository = $this->getStorage()->getFactory()->createInstanceOf(TokenRepositoryInterface::class, $this->getStorage());
        foreach($data['tokens'] as $tokenInfo) {
            $token = $this->getStorage()->getFactory()->createInstanceOf(TokenInterface::class);
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
