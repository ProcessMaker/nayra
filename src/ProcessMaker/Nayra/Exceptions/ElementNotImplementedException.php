<?php

namespace ProcessMaker\Nayra\Exceptions;

use DomainException;

/**
 * Thrown when try to load a non implemented BPMN tag
 */
class ElementNotImplementedException extends DomainException
{
}
