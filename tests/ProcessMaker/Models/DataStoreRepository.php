<?php

namespace ProcessMaker\Models;

use ProcessMaker\Nayra\Contracts\Bpmn\DataStoreInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\ProcessInterface;
use ProcessMaker\Nayra\Bpmn\RepositoryTrait;
use ProcessMaker\Nayra\Contracts\Repositories\DataStoreRepositoryInterface;

/**
 * ApplicationRepository
 *
 * @package ProcessMaker\Models
 */
class DataStoreRepository implements DataStoreRepositoryInterface
{

    use RepositoryTrait;

    /**
     * Load a application from a persistent storage.
     *
     * @return \ProcessMaker\Nayra\Contracts\Bpmn\DataStoreInterface
     */
    public function createDataStoreInstance()
    {
        return new DataStore;
    }

    /**
     * Load a application from a persistent storage.
     *
     * @param string $uid
     *
     * @return \ProcessMaker\Nayra\Contracts\Bpmn\DataStoreInterface
     */
    public function loadDataStoreByUid($uid)
    {

    }

    /**
     * Create or update a application to a persistent storage.
     *
     * @param \ProcessMaker\Nayra\Contracts\Bpmn\DataStoreInterface $dataStore
     * @param $saveChildElements
     *
     * @return $this
     */
    public function store(DataStoreInterface $dataStore, $saveChildElements = false)
    {

    }

    /**
     * Create an instance of the entity.
     *
     * @param ProcessInterface|null $process
     *
     * @return \ProcessMaker\Nayra\Contracts\Bpmn\EntityInterface
     */
    public function create(ProcessInterface $process = null)
    {
        return new DataStore($process);
    }
}
