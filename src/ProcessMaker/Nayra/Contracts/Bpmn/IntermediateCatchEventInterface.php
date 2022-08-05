<?php

namespace ProcessMaker\Nayra\Contracts\Bpmn;

/**
 * IntermediateCatchEvent interface.
 */
interface IntermediateCatchEventInterface extends CatchEventInterface
{
    /*
    * Events defined for the the throw event interface
    */
    const EVENT_CATCH_EXCEPTION = 'CatchEventException';

    const EVENT_CATCH_TOKEN_PASSED = 'CatchEventTokenPassed';

    const EVENT_CATCH_TOKEN_CONSUMED = 'CatchEventTokenConsumed';

    const EVENT_CATCH_MESSAGE_CATCH = 'CatchEventMessageCatch';

    const EVENT_CATCH_MESSAGE_CONSUMED = 'CatchEventMessageConsumed';

    const TOKEN_STATE_CLOSED = 'CLOSED';

    /**
     * Get the activation transition of the element
     *
     * @return TransitionInterface
     */
    public function getActivationTransition();

    /**
     * Get the active state of the element
     *
     * @return StateInterface
     */
    public function getActiveState();
}
