<?php

namespace ProcessMaker\Nayra\Bpmn;

use ProcessMaker\Nayra\Contracts\Bpmn\TokenInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\TransitionInterface;
use ProcessMaker\Nayra\Contracts\Engine\ExecutionInstanceInterface;

/**
 * Transition rule for a parallel gateway.
 *
 * @package ProcessMaker\Nayra\Bpmn
 */
class ParallelGatewayTransition implements TransitionInterface
{
    use TransitionTrait;

    /**
     * Initialize the tokens consumed property, the Parallel Gateway consumes
     * exactly one token from each incoming Sequence Flow.
     *
     */
    protected function initParallelGatewayTransition()
    {
        $this->setTokensConsumedPerTransition(-1);
        $this->setTokensConsumedPerIncoming(1);
    }

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
        return true;
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
        $incomingWithToken = $this->incoming()->find(function (Connection $flow) use ($executionInstance) {
            return $flow->originState()->getTokens($executionInstance)->count() > 0;
        });
        return $incomingWithToken->count() === $this->incoming()->count() && $incomingWithToken->count() > 0;
    }
}
