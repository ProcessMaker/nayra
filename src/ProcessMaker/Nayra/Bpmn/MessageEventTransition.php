<?php

namespace ProcessMaker\Nayra\Bpmn;

use ProcessMaker\Nayra\Contracts\Bpmn\TokenInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\TransitionInterface;
use ProcessMaker\Nayra\Contracts\Engine\ExecutionInstanceInterface;

class MessageEventTransition implements TransitionInterface
{
    use TransitionTrait;

    /**
     * Condition to be evaluate when executing the transition
     *
     * @param TokenInterface $token
     * @param ExecutionInstanceInterface $executionInstance
     * @return bool
     */
    public function assertCondition(TokenInterface $token, ExecutionInstanceInterface $executionInstance)
    {
        return false;
    }
}