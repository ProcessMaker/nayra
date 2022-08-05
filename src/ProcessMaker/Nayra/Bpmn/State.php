<?php

namespace ProcessMaker\Nayra\Bpmn;

use ProcessMaker\Nayra\Bpmn\StateTrait;
use ProcessMaker\Nayra\Contracts\Bpmn\StateInterface;

/**
 * State of a node in which tokens can be received.
 */
class State implements StateInterface
{
    use StateTrait;
}
