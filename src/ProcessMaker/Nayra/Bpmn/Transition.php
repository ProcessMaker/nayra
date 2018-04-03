<?php

namespace ProcessMaker\Nayra\Bpmn;

use ProcessMaker\Nayra\Bpmn\TransitionTrait;
use ProcessMaker\Nayra\Contracts\Bpmn\TokenInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\TransitionInterface;

/**
 * Transition rule that always pass the token.
 *
 * @package ProcessMaker\Nayra\Bpmn
 */
class Transition implements TransitionInterface
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
        return true;
    }
}
