<?php

namespace ProcessMaker\Nayra\Bpmn;

use ProcessMaker\Nayra\Contracts\Bpmn\ConditionedTransitionInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\TokenInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\TransitionInterface;
use ProcessMaker\Nayra\Contracts\Engine\ExecutionInstanceInterface;

/**
 * Verify the condition to transit and if not accomplished the tokens are consumed.
 *
 * @package ProcessMaker\Nayra\Bpmn
 */
class ConditionedTransition implements TransitionInterface, ConditionedTransitionInterface
{
    use TransitionTrait;

    /**
     * @var callable $condition
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
        $condition = $this->condition;
        return $condition($executionInstance->getDataStore()->getData());
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
     * @return boolean
     */
    protected function conditionIsFalse(ExecutionInstanceInterface $executionInstance)
    {
        $this->collect($executionInstance);
        return true;
    }

    /**
     * Consume the input tokens.
     *
     */
    private function collect(ExecutionInstanceInterface $executionInstance)
    {
        return $this->incoming()->sum(function (Connection $flow) use ($executionInstance) {
            return $flow->origin()->getTokens($executionInstance)->sum(function (TokenInterface $token) {
                return $token->getOwner()->consumeToken($token) ? 1 :0;
            });
        });
    }
}
