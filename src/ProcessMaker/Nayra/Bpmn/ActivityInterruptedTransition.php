<?php

namespace ProcessMaker\Nayra\Bpmn;

use ProcessMaker\Nayra\Contracts\Bpmn\ActivityInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\CollectionInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\TokenInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\TransitionInterface;
use ProcessMaker\Nayra\Contracts\Engine\ExecutionInstanceInterface;

/**
 * Transition rule when an activity is interrupted.
 *
 * @package ProcessMaker\Nayra\Bpmn
 */
class ActivityInterruptedTransition implements TransitionInterface
{
    use TransitionTrait;

    /**
     * Initialize transition.
     */
    protected function initActivityTransition()
    {
        $this->setPreserveToken(true);
        $this->setTokensConsumedPerIncoming(-1);
    }

    /**
     * Condition required to transit the element.
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
     * Merge tokens into one token.
     *
     * @param CollectionInterface|TokenInterface[] $consumeTokens
     *
     * @return TokenInterface|null
     */
    protected function mergeTokens(CollectionInterface $consumeTokens)
    {
        $properties = [];
        $chosenToken = null;
        $cancelableStates = [
            ActivityInterface::TOKEN_STATE_READY,
            ActivityInterface::TOKEN_STATE_ACTIVE,
            ActivityInterface::TOKEN_STATE_COMPLETED,
        ];
        foreach ($consumeTokens as $token) {
            $ownerName =$token->getOwner()->getName();
            if (in_array($ownerName, $cancelableStates)) {
                $chosenToken = $token;
            }
            $properties = array_merge($properties, $token->getProperties());
        }
        if ($chosenToken) {
            $chosenToken->setProperties($properties);
        }
        return $chosenToken;
    }

    /**
     * Evaluate true if an event requires to interrupt an activity (in ready, active or completed).
     *
     * @param ExecutionInstanceInterface $instance
     *
     * @return boolean
     */
    protected function hasAllRequiredTokens(ExecutionInstanceInterface $instance)
    {
        $hasInterruption = false;
        $hasToken = false;
        foreach ($this->incoming() as $flow) {
            $origin = $flow->origin();
            if ($origin->getName() === ActivityInterface::TOKEN_STATE_EVENT_INTERRUPTING_EVENT) {
                $hasInterruption = $origin->getTokens($instance)->count() >= 1;
            } else {
                $hasToken = $hasToken || $origin->getTokens($instance)->count() >= 1;
            }
        }
        return $hasInterruption && $hasToken;
    }
}
