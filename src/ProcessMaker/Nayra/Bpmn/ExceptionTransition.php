<?php

namespace ProcessMaker\Nayra\Bpmn;

use ProcessMaker\Nayra\Bpmn\TransitionTrait;
use ProcessMaker\Nayra\Contracts\Bpmn\ActivityInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\TokenInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\TransitionInterface;

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
     *
     * @return bool
     */
    public function assertCondition(TokenInterface $token)
    {
        return $token->getProperty('STATUS') === ActivityInterface::TOKEN_STATE_FAILING;
    }
}
