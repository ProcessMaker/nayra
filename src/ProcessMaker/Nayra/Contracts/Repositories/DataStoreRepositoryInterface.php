<?php

namespace ProcessMaker\Nayra\Contracts\Repositories;

use ProcessMaker\Nayra\Contracts\Bpmn\DataStoreInterface;

/**
 * Repository for DataStoreInterface
 *
 * @package ProcessMaker\Nayra\Contracts\Repositories
 */
interface DataStoreRepositoryInterface extends RepositoryInterface
{

    /**
     * Create an data store instance.
     *
     * @return \ProcessMaker\Nayra\Contracts\Bpmn\DataStoreInterface
     */
    public function createDataStoreInstance();

    /**
     * Load a data store from a persistent storage.
     *
     * @param string $uid
     *
     * @return \ProcessMaker\Nayra\Contracts\Bpmn\DataStoreInterface
     */
    public function loadDataStoreByUid($uid);

    /**
     * Create or update a data store to a persistent storage.
     *
     * @param \ProcessMaker\Nayra\Contracts\Bpmn\DataStoreInterface $dataStore
     * @param $saveChildElements
     *
     * @return $this
     */
    public function store(DataStoreInterface $dataStore, $saveChildElements=false);
}
