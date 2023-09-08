<?php

namespace ProcessMaker\Nayra\Exceptions;

use Exception;
use ProcessMaker\Nayra\Contracts\Bpmn\FlowNodeInterface;

/**
 * Thrown when try to load a non implemented BPMN tag
 */
class RuntimeException extends Exception
{
    /**
     * @var FlowNodeInterface
     */
    public $element;

    public function __construct($message = "", $code = 0, Exception $previous = null, FlowNodeInterface $element)
    {
        $this->element = $element;
        parent::__construct($message, $code, $previous);
    }
}
