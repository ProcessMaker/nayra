<?php

namespace ProcessMaker\Nayra\Bpmn;

use ProcessMaker\Nayra\Contracts\Bpmn\ActivityInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\CollectionInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\ConnectionInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\StateInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\TokenInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\TransitionInterface;
use ProcessMaker\Nayra\Contracts\Engine\ExecutionInstanceInterface;

/**
 * Transition rule that always pass the token.
 *
 * @package ProcessMaker\Nayra\Bpmn
 */
class DataOutputTransition implements TransitionInterface
{
    use TransitionTrait;

    /**
     * Initialize the transition.
     *
     * @param FlowNodeInterface $owner
     * @param bool $preserveToken
     */
    protected function initDataOutputTransition()
    {
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
        $loop = $this->getOwner()->getLoopCharacteristics();
        return !$loop || !$loop->isExecutable() || $loop->isLoopCompleted($executionInstance, $token);
    }

    /**
     * Get transition owner element
     *
     * @return ActivityInterface
     */
    public function getOwner()
    {
        return $this->owner;
    }

    /**
     * Activate the next state.
     *
     * @param \ProcessMaker\Nayra\Contracts\Bpmn\ConnectionInterface $flow
     * @param \ProcessMaker\Nayra\Contracts\Engine\ExecutionInstanceInterface $instance
     * @param \ProcessMaker\Nayra\Contracts\Bpmn\CollectionInterface $consumeTokens
     * @param array $properties
     * @param \ProcessMaker\Nayra\Contracts\Bpmn\TransitionInterface|null $source
     *
     * @return TokenInterface
     */
    protected function activateNextState(ConnectionInterface $flow, ExecutionInstanceInterface $instance, CollectionInterface $consumeTokens, array $properties = [], TransitionInterface $source = null)
    {
        $loop = $this->getOwner()->getLoopCharacteristics();
        if ($loop && $loop->isExecutable()) {
            //@todo merge output data
        }
        $nextState = $flow->targetState();
        $nextState->addNewToken($instance, $properties, $source);
    }
}
