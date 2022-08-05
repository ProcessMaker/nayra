<?php

namespace ProcessMaker\Nayra\Bpmn;

use ProcessMaker\Nayra\Contracts\Bpmn\TokenInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\TransitionInterface;
use ProcessMaker\Nayra\Contracts\Engine\ExecutionInstanceInterface;

/**
 * Transition rule to consume tokens in an End Event.
 */
class EndTransition implements TransitionInterface
{
    use TransitionTrait;

    /**
     * Condition required at end event.
     *
     * @param TokenInterface|null $token
     * @param ExecutionInstanceInterface|null $executionInstance
     *
     * @return bool
     */
    public function assertCondition(TokenInterface $token = null, ExecutionInstanceInterface $executionInstance = null)
    {
        return true;
    }

    /**
     * Check if transition has all the required tokens to be activated
     *
     * @param ExecutionInstanceInterface $instance
     *
     * @return bool
     */
    protected function hasAllRequiredTokens(ExecutionInstanceInterface $instance)
    {
        return $this->incoming()->count() > 0
                && $this->incoming()
                        ->find(function ($flow) use ($instance) {
                            return $flow->origin()->getTokens($instance)->count() === 0;
                        })
                        ->count() === 0;
    }
}
