<?php

namespace ProcessMaker\Models;

use ProcessMaker\Nayra\Bpmn\RepositoryTrait;
use ProcessMaker\Nayra\Contracts\Engine\ExecutionInstanceInterface;
use ProcessMaker\Nayra\Contracts\Repositories\ExecutionInstanceRepositoryInterface;
use ProcessMaker\Nayra\Engine\ExecutionInstance;

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
    private $data = [];

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
        $data = $this->data[$uid];
        $instance = new ExecutionInstance();
        $process = $this->getFactory()->getProcessRepository()->loadProcessByUid($data['processId']);
        $dataStore = $this->getFactory()->getDataStoreRepository()->createDataStoreInstance();
        $dataStore->setData($data['data']);
        $instance->setProcess($process);
        $instance->setDataStore($dataStore);
        $process->getTransitions($this->getFactory());

        //Load tokens:
        $tokenRepository = $this->getFactory()->getTokenRepository();
        foreach($data['tokens'] as $tokenInfo) {
            $token = $tokenRepository->createTokenInstance();
            $token->setProperties($tokenInfo);
            $element = $this->getFactory()->loadBpmElementById($tokenInfo['elementId']);
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
        $this->data = $data;
    }
}
