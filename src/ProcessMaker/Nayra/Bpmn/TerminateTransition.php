<?php

namespace ProcessMaker\Nayra\Bpmn;

use ProcessMaker\Nayra\Contracts\Bpmn\CollectionInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\EventDefinitionInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\TokenInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\TransitionInterface;
use ProcessMaker\Nayra\Contracts\Engine\ExecutionInstanceInterface;

/**
 * Transition rule to consume tokens in an Terminate Event.
 */
class TerminateTransition implements TransitionInterface
{
    use TransitionTrait {
        doTransit as doTransitTrait;
    }

    /**
     * @var \ProcessMaker\Nayra\Contracts\Bpmn\EventDefinitionInterface
     */
    private $eventDefinition;

    /**
     * Condition required to terminate the token.
     *
     * @param TokenInterface|null $token
     * @param ExecutionInstanceInterface|null $executionInstance
     *
     * @return bool
     */
    public function assertCondition(TokenInterface $token = null, ExecutionInstanceInterface $executionInstance = null)
    {
        return $this->eventDefinition->assertsRule($this->eventDefinition, $this->owner, $executionInstance);
    }

    /**
     * @param CollectionInterface $consumeTokens
     * @param ExecutionInstanceInterface $executionInstance
     *
     * @return bool
     */
    protected function doTransit(CollectionInterface $consumeTokens, ExecutionInstanceInterface $executionInstance)
    {
        // Terminate events must close all active tokens
        foreach ($executionInstance->getTokens() as $token) {
            if ($this->assertCondition($token, $executionInstance)) {
                $this->eventDefinition->execute($this->eventDefinition, $this->owner, $executionInstance, $token);
                $token->setStatus('CLOSED');
            }
        }

        return $this->doTransitTrait($consumeTokens, $executionInstance);
    }

    /**
     * Set the event definition that terminates the process.
     *
     * @param \ProcessMaker\Nayra\Contracts\Bpmn\EventDefinitionInterface $eventDefinition
     */
    public function setEventDefinition(EventDefinitionInterface $eventDefinition)
    {
        $this->eventDefinition = $eventDefinition;
    }
}
