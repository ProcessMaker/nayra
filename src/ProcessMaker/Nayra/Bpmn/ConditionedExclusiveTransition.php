<?php

namespace ProcessMaker\Nayra\Bpmn;

use ProcessMaker\Nayra\Contracts\Bpmn\ConditionedTransitionInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\TokenInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\TransitionInterface;
use ProcessMaker\Nayra\Contracts\Engine\ExecutionInstanceInterface;

/**
 * Verify the condition to transit following the exclusive transition rules.
 * If not accomplished the tokens are consumed.
 *
 * @package ProcessMaker\Nayra\Bpmn
 */
class ConditionedExclusiveTransition implements TransitionInterface, ConditionedTransitionInterface
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
        $result = false;
        $myIndex = $this->owner->getConditionedTransitions()->indexOf($this);
        $condition = $this->condition;
        $myCondition = $condition($executionInstance->getDataStore()->getData());

        $firstIndexTrue = $myIndex;
        if ($myCondition) {
            //find the first condition that evaluates to true
            foreach ($this->owner->getConditionedTransitions() as $index => $transition) {
                if ($index >= $myIndex) {
                    break;
                }
                if ($transition->assertCondition($token, $executionInstance)) {
                    $firstIndexTrue = $index;
                    break;
                }
            }

            //the transition will be executed just if this transition is the first one of all transitions that are
            //evaluated to true.
            $result = $myIndex === $firstIndexTrue;
        }
        return $result;
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
                return $token->getOwner()->consumeToken($token) ? 1 : 0;
            });
        });
    }
}

