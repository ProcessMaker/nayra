<?php

namespace ProcessMaker\Nayra\Bpmn;

use ProcessMaker\Nayra\Bpmn\TransitionTrait;
use ProcessMaker\Nayra\Contracts\Bpmn\ActivityInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\TokenInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\TransitionInterface;
use ProcessMaker\Nayra\Contracts\Engine\ExecutionInstanceInterface;

/**
 * Transition rule for an activity in FAILING state.
 *
 * @package ProcessMaker\Nayra\Bpmn
 */
class CloseExceptionTransition implements TransitionInterface
{
    use TransitionTrait;

    /**
     * Condition required to transit an activity in FAILING state.
     *
     * @param TokenInterface $token
     * @param ExecutionInstanceInterface $executionInstance
     *
     * @return bool
     */
    public function assertCondition(TokenInterface $token, ExecutionInstanceInterface $executionInstance)
    {
        return $token->getStatus() === ActivityInterface::TOKEN_STATE_COMPLETED;
    }
}
