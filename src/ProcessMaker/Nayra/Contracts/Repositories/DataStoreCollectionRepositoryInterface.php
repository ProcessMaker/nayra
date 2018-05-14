<?php

namespace ProcessMaker\Nayra\Contracts\Repositories;


interface DataStoreCollectionRepositoryInterface extends RepositoryInterface
{
    /**
     * Create an data store collection instance.
     *
     * @return \ProcessMaker\Nayra\Contracts\Bpmn\DataStoreCollectionInterface
     */
    public function createDataStoreCollectionInstance();
}

