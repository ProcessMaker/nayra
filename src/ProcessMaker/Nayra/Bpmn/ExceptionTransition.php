<?php

namespace ProcessMaker\Nayra\Bpmn;

use ProcessMaker\Nayra\Bpmn\TransitionTrait;
use ProcessMaker\Nayra\Contracts\Bpmn\ActivityInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\TokenInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\TransitionInterface;
use ProcessMaker\Nayra\Contracts\Engine\ExecutionInstanceInterface;

/**
 * Transition rule when a exception is catch.
 *
 * @package ProcessMaker\Nayra\Bpmn
 */
class ExceptionTransition implements TransitionInterface
{
    use TransitionTrait;

    /**
     * Condition required to transit the element.
     *
     * @param TokenInterface $token
     * @param ExecutionInstanceInterface $executionInstance
     *
     * @return bool
     */
    public function assertCondition(TokenInterface $token, ExecutionInstanceInterface $executionInstance)
    {
        return $token->getStatus() === ActivityInterface::TOKEN_STATE_FAILING;
    }
}
