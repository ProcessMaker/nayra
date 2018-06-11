<?php

namespace ProcessMaker\Nayra\Bpmn\Models;

use ProcessMaker\Nayra\Bpmn\Collection;
use ProcessMaker\Nayra\Contracts\Bpmn\DataStoreCollectionInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\DataStoreInterface;

/**
 * DataStore collection.
 *
 */
class DataStoreCollection extends Collection implements DataStoreCollectionInterface
{

    /**
     * Add element to the collection.
     *
     * @param DataStoreInterface $element
     *
     * @return $this
     */
    public function add(DataStoreInterface $element)
    {
        $this->push($element);
        return $this;
    }
}
