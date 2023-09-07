<?php

namespace ProcessMaker\Nayra\Exceptions;

use Exception;
use ProcessMaker\Nayra\Contracts\Bpmn\FlowNodeInterface;

/**
 * Thrown when try to load a non implemented BPMN tag
 */
class RuntimeException extends Exception
{
    public function __construct($message = "", $code = 0, Exception $previous = null, public FlowNodeInterface $element)
    {
        parent::__construct($message, $code, $previous);
    }
}
