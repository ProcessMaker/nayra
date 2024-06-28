<?php

namespace ProcessMaker\Nayra\Bpmn;

use ProcessMaker\Nayra\Contracts\Bpmn\ConditionedTransitionInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\TokenInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\TransitionInterface;
use ProcessMaker\Nayra\Contracts\Engine\ExecutionInstanceInterface;

/**
 * Verify the condition to transit and if not accomplished the tokens are consumed.
 */
class ConditionedTransition implements TransitionInterface, ConditionedTransitionInterface
{
    use TransitionTrait;
    use ForceGatewayTransitionTrait;

    /**
     * @var callable
     */
    private $condition;

    /**
     * Condition required to transit the element.
     *
     * @param TokenInterface|null $token
     * @param ExecutionInstanceInterface|null $executionInstance
     *
     * @return mixed
     */
    public function assertCondition(TokenInterface $token = null, ExecutionInstanceInterface $executionInstance = null)
    {
        // If debug mode is enabled, the transition is triggered only if it is selected
        if ($executionInstance && $this->shouldDebugTriggerThisTransition($executionInstance)) {
            return true;
        }
        // If debug mode is enabled, the transition is not triggered if it is not selected
        if ($executionInstance && $this->shouldDebugSkipThisTransition($executionInstance)) {
            return false;
        }

        $condition = $this->condition;
        $dataStore = $executionInstance ? $executionInstance->getDataStore()
            : $this->getOwnerProcess()->getEngine()->getDataStore();

        return $condition($dataStore->getData());
    }

    /**
     * Set the transit condition.
     *
     * @param callable $condition
     *
     * @return $this
     */
    public function setCondition(callable $condition)
    {
        $this->condition = $condition;

        return $this;
    }

    /**
     * If the condition is not met.
     *
     * @param \ProcessMaker\Nayra\Contracts\Engine\ExecutionInstanceInterface $executionInstance
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
     * @param \ProcessMaker\Nayra\Contracts\Engine\ExecutionInstanceInterface $executionInstance
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
