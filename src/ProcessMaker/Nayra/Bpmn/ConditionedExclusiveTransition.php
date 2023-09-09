<?php

namespace ProcessMaker\Nayra\Bpmn;

use ProcessMaker\Nayra\Contracts\Bpmn\ConditionedTransitionInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\TokenInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\TransitionInterface;
use ProcessMaker\Nayra\Contracts\Engine\ExecutionInstanceInterface;
use ProcessMaker\Nayra\Exceptions\RuntimeException;
use Throwable;

/**
 * Verify the condition to transit following the exclusive transition rules.
 * If not accomplished the tokens are consumed.
 */
class ConditionedExclusiveTransition implements TransitionInterface, ConditionedTransitionInterface
{
    use TransitionTrait;

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
        try {
            $result = false;
            $myIndex = $this->owner->getConditionedTransitions()->indexOf($this);
            $condition = $this->condition;
            $dataStore = $executionInstance ? $executionInstance->getDataStore()
                : $this->getOwnerProcess()->getEngine()->getDataStore();
            $myCondition = $condition($dataStore->getData());

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
        } catch (Throwable $exception) {
            throw new RuntimeException($exception->getMessage(), $exception->getCode(), $exception, $this->owner);
        }
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
