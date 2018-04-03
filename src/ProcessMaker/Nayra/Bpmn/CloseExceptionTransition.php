<?php

namespace ProcessMaker\Nayra\Bpmn;

use ProcessMaker\Nayra\Bpmn\TransitionTrait;
use ProcessMaker\Nayra\Contracts\Bpmn\ActivityInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\TokenInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\TransitionInterface;

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
     *
     * @return bool
     */
    protected function assertCondition(TokenInterface $token)
    {
        return $token->getProperty('STATUS') === ActivityInterface::TOKEN_STATE_COMPLETED;
    }
}
