<?php

namespace ProcessMaker\Nayra\Bpmn;

use ProcessMaker\Nayra\Contracts\Bpmn\TokenInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\TransitionInterface;
use ProcessMaker\Nayra\Contracts\Engine\ExecutionInstanceInterface;

/**
 * Transition rule to consume tokens in an End Event.
 *
 * @package ProcessMaker\Nayra\Bpmn
 */
class IntermediateCatchEventTransition implements TransitionInterface
{
    use TransitionTrait;

    /**
     * Condition required at end event.
     *
     * @param TokenInterface $token
     *
     * @return bool
     */
    public function assertCondition(TokenInterface $token = null, ExecutionInstanceInterface $executionInstance = null)
    {
        return true;
    }
}
