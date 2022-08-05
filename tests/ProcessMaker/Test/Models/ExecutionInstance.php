<?php

namespace ProcessMaker\Test\Models;

use ProcessMaker\Nayra\Contracts\Engine\ExecutionInstanceInterface;
use ProcessMaker\Nayra\Engine\ExecutionInstanceTrait;

/**
 * Execution instance for the engine.
 */
class ExecutionInstance implements ExecutionInstanceInterface
{
    use ExecutionInstanceTrait;

    /**
     * Initialize a token class with unique id.
     */
    protected function initToken()
    {
        $this->setId(uniqid());
    }
}
