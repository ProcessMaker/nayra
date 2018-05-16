<?php

namespace ProcessMaker\Nayra\Contracts\Bpmn;

/**
 * IntermediateCatchEvent interface.
 *
 * @package ProcessMaker\Nayra\Contracts\Bpmn
 */
interface IntermediateCatchEventInterface extends CatchEventInterface
{
    /*
    * Events defined for the the throw event interface
    */
    const EVENT_CATCH_TOKEN_ARRIVES = 'CatchEventTokenArrives';
    const EVENT_CATCH_EXCEPTION = 'CatchEventException';
    const EVENT_CATCH_TOKEN_PASSED = 'CatchEventTokenPassed';
    const EVENT_CATCH_TOKEN_CONSUMED = 'CatchEventTokenConsumed';
    const EVENT_CATCH_TOKEN_CATCH = 'CatchEventTokenCatch';

}
