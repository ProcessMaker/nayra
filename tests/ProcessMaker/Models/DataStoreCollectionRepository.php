<?php

namespace ProcessMaker\Models;


use ProcessMaker\Nayra\Bpmn\RepositoryTrait;
use ProcessMaker\Nayra\Contracts\Bpmn\CollectionInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\DataStoreCollectionInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\DataStoreInterface;
use ProcessMaker\Nayra\Contracts\Repositories\DataStoreCollectionRepositoryInterface;

class DataStoreCollectionRepository implements DataStoreCollectionRepositoryInterface
{
    use RepositoryTrait;


    /**
     * Create an data store collection instance.
     *
     * @return \ProcessMaker\Nayra\Contracts\Bpmn\DataStoreCollectionInterface
     */
    public function createDataStoreCollectionInstance()
    {
        return new DataStoreCollection();
    }
}
