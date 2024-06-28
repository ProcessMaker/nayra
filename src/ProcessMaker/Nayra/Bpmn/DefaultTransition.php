<?php

namespace ProcessMaker\Nayra\Bpmn;

use ProcessMaker\Nayra\Contracts\Bpmn\GatewayInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\TokenInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\TransitionInterface;
use ProcessMaker\Nayra\Contracts\Engine\ExecutionInstanceInterface;

/**
 * Verify the condition to transit and if not accomplished the tokens are consumed.
 */
class DefaultTransition implements TransitionInterface
{
    use TransitionTrait;
    use ForceGatewayTransitionTrait;

    /**
     * Returns true if the condition of the transition is met with the DataStore of the passed execution instance
     *
     * @param TokenInterface|null $token
     * @param ExecutionInstanceInterface|null $executionInstance
     *
     * @return bool
     */
    public function assertCondition(TokenInterface $token = null, ExecutionInstanceInterface $executionInstance = null)
    {
        // If debug mode is enabled, the transition is triggered only if it is selected
        if ($this->shouldDebugTriggerThisTransition($executionInstance)) {
            return true;
        }
        // If debug mode is enabled, the transition is not triggered if it is not selected
        if ($this->shouldDebugSkipThisTransition($executionInstance)) {
            return false;
        }

        $executeDefaultTransition = true;
        foreach ($this->owner->getConditionedTransitions() as $transition) {
            if ($transition->assertCondition($token, $executionInstance)) {
                $executeDefaultTransition = false;
                break;
            }
        }

        return $executeDefaultTransition;
    }

    /**
     * If the condition is not met.
     *
     * @param ExecutionInstanceInterface $executionInstance
     *
     * @return bool
     */
    protected function conditionIsFalse(ExecutionInstanceInterface $executionInstance)
    {
        $this->collect($executionInstance);

        return true;
    }

    /**
     * Consume the input tokens.
     *
     * @param ExecutionInstanceInterface $executionInstance
     *
     * @return int
     */
    private function collect(ExecutionInstanceInterface $executionInstance)
    {
        return $this->incoming()->sum(function (Connection $flow) use ($executionInstance) {
            return $flow->origin()->getTokens($executionInstance)->sum(function (TokenInterface $token) {
                return $token->getOwner()->consumeToken($token) ? 1 : 0;
            });
        });
    }
}
