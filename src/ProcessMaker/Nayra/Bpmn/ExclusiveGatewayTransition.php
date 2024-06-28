<?php

namespace ProcessMaker\Nayra\Bpmn;

use ProcessMaker\Nayra\Contracts\Bpmn\TokenInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\TransitionInterface;
use ProcessMaker\Nayra\Contracts\Engine\ExecutionInstanceInterface;

/**
 * Transition rule for a inclusive gateway.
 */
class ExclusiveGatewayTransition implements TransitionInterface
{
    use TransitionTrait;
    use PauseOnGatewayTransitionTrait;

    /**
     * Initialize the tokens consumed property, the Exclusive Gateway consumes
     * exactly one token from each transition.
     */
    protected function initExclusiveGatewayTransition()
    {
        $this->setTokensConsumedPerTransition(1);
        $this->setTokensConsumedPerIncoming(1);
    }

    /**
     * Always true because any token that arrives triggers the gateway
     * outgoing flow transition.
     *
     * @param TokenInterface|null $token
     * @param ExecutionInstanceInterface|null $executionInstance
     *
     * @return bool
     */
    public function assertCondition(TokenInterface $token = null, ExecutionInstanceInterface $executionInstance = null)
    {
        // Execution is paused if Engine is in demo mode and the gateway choose is not selected
        return !$this->shouldPauseGatewayTransition($executionInstance);
    }

    /**
     * In this gateway,  one token should arrive, and every time this happens the gateway is ready to be triggered
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

        $withToken = $this->incoming()->find(function (Connection $flow) use ($executionInstance) {
            return $flow->originState()->getTokens($executionInstance)->count() > 0;
        });

        return $withToken->count() > 0;
    }
}
