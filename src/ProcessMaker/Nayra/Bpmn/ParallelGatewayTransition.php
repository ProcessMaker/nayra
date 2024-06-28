<?php

namespace ProcessMaker\Nayra\Bpmn;

use ProcessMaker\Nayra\Contracts\Bpmn\TokenInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\TransitionInterface;
use ProcessMaker\Nayra\Contracts\Engine\ExecutionInstanceInterface;

/**
 * Transition rule for a parallel gateway.
 */
class ParallelGatewayTransition implements TransitionInterface
{
    use TransitionTrait;
    use PauseOnGatewayTransitionTrait;

    /**
     * Always true because the conditions are not defined in the gateway, but for each
     * outgoing flow transition.
     *
     * @param TokenInterface|null $token
     * @param ExecutionInstanceInterface|null $executionInstance
     * @return bool
     */
    public function assertCondition(TokenInterface $token = null, ExecutionInstanceInterface $executionInstance = null)
    {
        return !$this->shouldPauseGatewayTransition($executionInstance);
    }

    /**
     * The Parallel Gateway is activated if there is at least one token on
     * each incoming Sequence Flow.
     *
     * @param \ProcessMaker\Nayra\Contracts\Engine\ExecutionInstanceInterface $executionInstance
     *
     * @return bool
     */
    protected function hasAllRequiredTokens(ExecutionInstanceInterface $executionInstance)
    {
        if ($this->doesDemoHasAllRequiredTokens($executionInstance)) {
            return true;
        }

        $incomingWithToken = $this->incoming()->find(function (Connection $flow) use ($executionInstance) {
            return $flow->originState()->getTokens($executionInstance)->count() > 0;
        });

        return $incomingWithToken->count() === $this->incoming()->count() && $incomingWithToken->count() > 0;
    }
}
