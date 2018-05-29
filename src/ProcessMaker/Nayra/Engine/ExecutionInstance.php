<?php

namespace ProcessMaker\Nayra\Engine;

use ProcessMaker\Nayra\Contracts\Engine\ExecutionInstanceInterface;

/**
 * Execution instance for the engine.
 *
 * @package ProcessMaker\Nayra\Bpmn
 */
class ExecutionInstance implements ExecutionInstanceInterface
{

    use ExecutionInstanceTrait;
}
