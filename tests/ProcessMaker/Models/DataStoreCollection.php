<?php

namespace ProcessMaker\Models;

use ProcessMaker\Nayra\Bpmn\Collection;
use ProcessMaker\Nayra\Contracts\Bpmn\DataStoreCollectionInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\DataStoreInterface;

class DataStoreCollection extends Collection implements DataStoreCollectionInterface
{

    public function add(DataStoreInterface $element)
    {
        $this->push($element);
        return $this;
    }
}
