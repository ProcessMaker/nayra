<?php

namespace ProcessMaker\Nayra\Exceptions;

use OutOfBoundsException;

/**
 * Thrown when try to get an element that is not found in the BPMN definitions.
 *
 */
class ElementNotFoundException extends OutOfBoundsException
{

    /**
     * Exception constructor.
     *
     * @param string $id
     */
    public function __construct($id)
    {
        parent::__construct('Element instance for id "' . $id . '" was not found');
    }
}
