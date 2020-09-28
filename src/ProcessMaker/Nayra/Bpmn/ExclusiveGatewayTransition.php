<?php

namespace ProcessMaker\Nayra\Bpmn;

use ProcessMaker\Nayra\Contracts\Bpmn\TokenInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\TransitionInterface;
use ProcessMaker\Nayra\Contracts\Engine\ExecutionInstanceInterface;

/**
 * Transition rule for a inclusive gateway.
 *
 * @package ProcessMaker\Nayra\Bpmn
 */
class ExclusiveGatewayTransition implements TransitionInterface
{
    use TransitionTrait;

    /**
     * Initialize the tokens consumed property, the Exclusive Gateway consumes
     * exactly one token from each transition.
     *
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
        return true;
    }

    /**
     * In this gateway,  one token should arrive, and every time this happens the gateway is ready to be triggered
     *
     * @return bool
     */
    protected function hasAllRequiredTokens(ExecutionInstanceInterface $executionInstance)
    {
        $withToken = $this->incoming()->find(function (Connection $flow) use ($executionInstance) {
            return $flow->originState()->getTokens($executionInstance)->count()>0;
        });
        $rule = $withToken->count()>0;
        return $rule;
    }
}
