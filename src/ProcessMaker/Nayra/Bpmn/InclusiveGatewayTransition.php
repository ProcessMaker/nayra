<?php

namespace ProcessMaker\Nayra\Bpmn;

use ProcessMaker\Nayra\Contracts\Bpmn\StateInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\TokenInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\TransitionInterface;
use ProcessMaker\Nayra\Contracts\Engine\ExecutionInstanceInterface;
use ProcessMaker\Nayra\Engine\DemoModeTrait;

/**
 * Transition rule for a inclusive gateway.
 */
class InclusiveGatewayTransition implements TransitionInterface
{
    use TransitionTrait;
    use PauseOnGatewayTransitionTrait;

    /**
     * Initialize the tokens consumed property, the Inclusive Gateway consumes
     * a token from each incoming Sequence Flow that has a token.
     */
    protected function initExclusiveGatewayTransition()
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
        // Execution is paused if Engine is in demo mode and the gateway choose is not selected
        return !$this->shouldPauseGatewayTransition($executionInstance);
    }

    /**
     * The Inclusive Gateway has all the required tokens if
     *   • At least one incoming Sequence Flow has at least one token and
     *   • For every directed path formed by sequence flow that
     *     - starts with a Sequence Flow f of the diagram that has a token,
     *     - ends with an incoming Sequence Flow of the inclusive gateway that has no token, and
     *     - does not visit the Inclusive Gateway.
     *   • There is also a directed path formed by Sequence Flow that - starts with f,
     *     - ends with an incoming Sequence Flow of the inclusive gateway that has a token, and
     *     - does not visit the Inclusive Gateway.
     *
     * @param \ProcessMaker\Nayra\Contracts\Engine\ExecutionInstanceInterface $executionInstance
     *
     * @return bool
     */
    protected function hasAllRequiredTokens(ExecutionInstanceInterface $executionInstance)
    {
        $withToken = $this->incoming()->find(function (Connection $flow) use ($executionInstance) {
            return $flow->originState()->getTokens($executionInstance)->count() > 0;
        });
        $withoutToken = $this->incoming()->find(function (Connection $flow) use ($executionInstance) {
            return $flow->originState()->getTokens($executionInstance)->count() === 0;
        });
        $rule1 = $withToken->count() > 0;
        $rule2 = $withoutToken->find(function ($inFlow) use ($executionInstance) {
            $paths = $inFlow->origin()->paths(function (Connection $flow) use ($inFlow, $executionInstance) {
                return $flow !== $inFlow
                        && $flow->origin() instanceof StateInterface
                        && $flow->originState()->getTokens($executionInstance)->count() > 0;
            }, function (Connection $flow) {
                return $flow->origin() !== $this; //does not visit
            });

            return $paths->count() !== 0;
        })->count() === 0;
        $rule3 = $withToken->find(function ($inFlow) use ($executionInstance) {
            return $inFlow->origin()->paths(function (Connection $flow) use ($inFlow, $executionInstance) {
                return $flow !== $inFlow
                            && $flow->origin() instanceof StateInterface
                            && $flow->originState()->getTokens($executionInstance)->count() > 0;
            }, function (Connection $flow) {
                return $flow->origin() !== $this; //does not visit
            })->count() !== 0;
        })->count() === 0;

        return $rule1 && $rule2 && $rule3;
    }
}
