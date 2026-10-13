<?php

namespace ProcessMaker\Nayra\Bpmn;

use ProcessMaker\Nayra\Contracts\Bpmn\TokenInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\TransitionInterface;
use ProcessMaker\Nayra\Contracts\Engine\ExecutionInstanceInterface;

/**
 * Transition rule to consume tokens in an Intermediate Throw Event.
 *
 * An event is not a join: every token that arrives through any incoming flow
 * triggers the event, without waiting for tokens in the other incoming flows.
 */
class IntermediateThrowEventTransition implements TransitionInterface
{
    use TransitionTrait;

    /**
     * Initialize the transition, one token is consumed each time the event is triggered.
     */
    protected function initIntermediateThrowEventTransition()
    {
        $this->setTokensConsumedPerTransition(1);
        $this->setTokensConsumedPerIncoming(1);
    }

    /**
     * Condition required at the intermediate throw event.
     *
     * @param \ProcessMaker\Nayra\Contracts\Bpmn\TokenInterface|null $token
     * @param \ProcessMaker\Nayra\Contracts\Engine\ExecutionInstanceInterface|null $executionInstance
     *
     * @return bool
     */
    public function assertCondition(TokenInterface $token = null, ExecutionInstanceInterface $executionInstance = null)
    {
        return true;
    }

    /**
     * The transition is ready as soon as any incoming flow has a token (FOUR-6787).
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

        return $withToken->count() > 0;
    }
}
