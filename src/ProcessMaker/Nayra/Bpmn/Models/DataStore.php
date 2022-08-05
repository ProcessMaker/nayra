<?php

namespace ProcessMaker\Nayra\Bpmn\Models;

use ProcessMaker\Nayra\Bpmn\DataStoreTrait;
use ProcessMaker\Nayra\Contracts\Bpmn\DataStoreInterface;

/**
 * Application
 */
class DataStore implements DataStoreInterface
{
    use DataStoreTrait;
}
