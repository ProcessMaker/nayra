<?php

namespace ProcessMaker\Nayra\Bpmn;

use ProcessMaker\Nayra\Contracts\Bpmn\ActivityInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\CollectionInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\ConnectionInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\ErrorInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\TokenInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\TransitionInterface;
use ProcessMaker\Nayra\Contracts\Engine\ExecutionInstanceInterface;

/**
 * Transition to check if the activity is a loop with an invalid data input
 */
class InvalidDataInputTransition implements TransitionInterface
{
    use TransitionTrait;

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

        return $loop && $loop->isExecutable() && !$loop->isDataInputValid($executionInstance, $token);
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
            foreach ($consumeTokens as $token) {
                $errorMessage = $loop->getDataInputError($instance, $token);
            }
            $error = $this->getOwner()->getRepository()->createError();
            $error->setId('INVALID_DATA_INPUT');
            $error->setName($errorMessage);
            $properties['error'] = $error;
            $properties[TokenInterface::BPMN_PROPERTY_EVENT_ID] = null;
            $properties[TokenInterface::BPMN_PROPERTY_EVENT_DEFINITION_CAUGHT] = null;
            $properties[TokenInterface::BPMN_PROPERTY_EVENT_TYPE] = ErrorInterface::class;
            $flow->targetState()->addNewToken($instance, $properties, $source);
        }
    }
}
