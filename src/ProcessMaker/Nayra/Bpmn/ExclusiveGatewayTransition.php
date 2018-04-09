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
     * Always true because any token that arrives triggers the gateway
     * outgoing flow transition.
     *
     * @return bool
     */
    public function assertCondition(TokenInterface $token, ExecutionInstanceInterface $executionInstance)
    {
        return true;
    }

    /**
     * In this gateway,  one token should arrive, and every time this happens the gateway is ready to be triggered
     *
     * @return bool
     */
    protected function hasAllRequiredTokens()
    {
        $withToken = $this->incoming()->find(function(Connection $flow){
            return $flow->originState()->getTokens()->count()>0;
        });
        $rule = $withToken->count()>0;
        return $rule;
    }
}

