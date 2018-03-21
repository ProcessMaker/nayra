<?php

namespace ProcessMaker\Nayra\Bpmn;

use ProcessMaker\Nayra\Contracts\Bpmn\TokenInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\TransitionInterface;
use ProcessMaker\Nayra\Bpmn\TransitionTrait;

/**
 * Transition rule for an activity.
 *
 * @package ProcessMaker\Nayra\Bpmn
 */
class ActivityTransition implements TransitionInterface
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
        return $token->getProperty('STATUS')==='COMPLETED';
    }
}
