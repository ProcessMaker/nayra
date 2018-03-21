<?php

namespace ProcessMaker\Nayra\Bpmn;

use ProcessMaker\Nayra\Contracts\Bpmn\StateInterface;
use ProcessMaker\Nayra\Bpmn\StateTrait;

/**
 * State of a node in which tokens can be received.
 *
 * @package ProcessMaker\Nayra\Bpmn
 */
class State implements StateInterface
{

    use StateTrait;
}
