<?php

namespace ProcessMaker\Nayra\Bpmn;

use ProcessMaker\Nayra\Contracts\Bpmn\TokenInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\TransitionInterface;
use ProcessMaker\Nayra\Bpmn\TransitionTrait;

/**
 * Transition rule to consume tokens in an End Event.
 *
 * @package ProcessMaker\Nayra\Bpmn
 */
class EndTransition implements TransitionInterface
{

    use TransitionTrait;

    /**
     * Condition required at end event.
     *
     * @param TokenInterface $token
     *
     * @return bool
     */
    protected function assertCondition(TokenInterface $token)
    {
        return true;
    }
}
