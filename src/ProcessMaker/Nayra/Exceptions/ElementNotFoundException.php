<?php

namespace ProcessMaker\Nayra\Exceptions;

use OutOfBoundsException;

/**
 * Thrown when try to get an element that is not found in the BPMN definitions.
 */
class ElementNotFoundException extends OutOfBoundsException
{
    public $elementId;

    /**
     * Exception constructor.
     *
     * @param string $id
     */
    public function __construct($id)
    {
        $this->elementId = $id;
        parent::__construct('Element "' . $id . '" was not found');
    }
}
