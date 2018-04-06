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
     * The Parallel Gateway consumes exactly one token from each incoming Sequence Flow
     * and produces exactly one token at each outgoing Sequence Flow.
     * If there are excess tokens at an incoming Sequence Flow, these tokens remain at
     * this Sequence Flow after execution of the Gateway.
     *
     * @var int $tokensConsumedPerTransition
     */
    protected $tokensConsumedPerTransition = 1;

    /**
     * Always true because the conditions are not defined in the gateway, but for each
     * outgoing flow transition.
     *
     * @return bool
     */
    public function assertCondition(TokenInterface $token, ExecutionInstanceInterface $executionInstance)
    {
        return true;
    }

    /**
     * The Parallel Gateway is activated if there is at least one token on
     * each incoming Sequence Flow.
     *
     * @return bool
     */
    protected function hasAllRequiredTokens()
    {
        $incomingWithToken = $this->incoming()->find(function(Connection $flow){
            return $flow->originState()->getTokens()->count()>0;
        });
        return $incomingWithToken->count() === $this->incoming()->count();
    }
}