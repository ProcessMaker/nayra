<?php

namespace ProcessMaker\Nayra\Bpmn\Models;

use ProcessMaker\Nayra\Bpmn\TokenTrait;
use ProcessMaker\Nayra\Contracts\Bpmn\TokenInterface;

/**
 * Token implementation.
 */
class Token implements TokenInterface
{
    use TokenTrait;

    /**
     * Initialize a token class with unique id.
     */
    protected function initToken()
    {
        $this->setId(uniqid());
    }
}
