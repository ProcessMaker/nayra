<?php

namespace ProcessMaker\Nayra\Contracts\Bpmn;

/**
 * IntermediateThrowEvent interface.
 *
 * @package ProcessMaker\Nayra\Contracts\Bpmn
 */
interface IntermediateThrowEventInterface extends ThrowEventInterface
{
    /*
    * Events defined for the the throw event interface
    */
    const EVENT_THROW_TOKEN_ARRIVES = 'ThrowEventTokenArrives';
    const EVENT_THROW_EXCEPTION = 'ThrowEventException';
    const EVENT_THROW_TOKEN_PASSED = 'ThrowEventTokenPassed';
    const EVENT_THROW_TOKEN_CONSUMED = 'ThrowEventTokenConsumed';
}
