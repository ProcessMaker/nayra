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
     * @param TokenInterface $token
     * @param ExecutionInstanceInterface $executionInstance
     *
     * @return mixed
     */
    public function assertCondition(TokenInterface $token, ExecutionInstanceInterface $executionInstance)
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
    protected function conditionIsFalse()
    {
        $this->collect();
        return true;
    }

    /**
     * Consume the input tokens.
     *
     */
    private function collect()
    {
        return $this->incoming()->sum(function (Connection $flow) {
            return $flow->origin()->getTokens()->sum(function (TokenInterface $token) {
                return $token->getOwner()->consumeToken($token) ? 1 :0;
            });
        });
    }

}