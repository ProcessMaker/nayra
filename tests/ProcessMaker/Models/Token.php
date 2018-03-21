<?php

namespace ProcessMaker\Models;

use ProcessMaker\Nayra\Bpmn\TokenTrait;

use ProcessMaker\Nayra\Contracts\Bpmn\TokenInterface;

/**
 * Token implementation.
 *
 * @package ProcessMaker\Models
 */
class Token implements TokenInterface
{
    use TokenTrait,
        LocalProcessTrait,
        LocalPropertiesTrait;
}