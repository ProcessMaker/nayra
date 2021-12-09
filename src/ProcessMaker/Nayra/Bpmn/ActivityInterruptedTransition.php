<?php

namespace ProcessMaker\Nayra\Bpmn;

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
     * @param CollectionInterface|TokenInterface[] $tokens
     *
     * @return TokenInterface|null
     */
    protected function mergeTokens(CollectionInterface $consumeTokens)
    {
        $properties = [];
        $chosenToken = null;
        foreach ($consumeTokens as $token) {
            if ($token->getOwnerElement() === $this->getOwner()) {
                $chosenToken = $token;
            }
            $properties = array_merge($properties, $token->getProperties());
        }
        if ($chosenToken) {
            $chosenToken->setProperties($properties);
        }
        return $chosenToken;
    }
}
