<?php

namespace ProcessMaker\Nayra\Bpmn;

use ProcessMaker\Nayra\Contracts\Bpmn\TokenInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\TransitionInterface;
use ProcessMaker\Nayra\Bpmn\TransitionTrait;
use ProcessMaker\Nayra\Contracts\Engine\ExecutionInstanceInterface;

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
     * @param ExecutionInstanceInterface $executionInstance
     *
     * @return bool
     */
    public function assertCondition(TokenInterface $token = null, ExecutionInstanceInterface $executionInstance = null)
    {
        return true;
    }

    protected function hasAllRequiredTokens(ExecutionInstanceInterface $instance)
    {
        return $this->incoming()->count() > 0
                && $this->incoming()
                        ->find(function ($flow) use ($instance) {
                            return $flow->origin()->getTokens($instance)->count() === 0;})
                        ->count() === 0;
    }
}
