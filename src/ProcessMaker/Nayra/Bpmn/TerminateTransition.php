<?php

namespace ProcessMaker\Nayra\Bpmn;

use ProcessMaker\Nayra\Bpmn\Collection;
use ProcessMaker\Nayra\Bpmn\TransitionTrait;
use ProcessMaker\Nayra\Contracts\Bpmn\EventDefinitionInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\TokenInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\TransitionInterface;
use ProcessMaker\Nayra\Contracts\Engine\ExecutionInstanceInterface;

/**
 * Transition rule to consume tokens in an Terminate Event.
 *
 * @package ProcessMaker\Nayra\Bpmn
 */
class TerminateTransition implements TransitionInterface
{

    use TransitionTrait;

    /**
     * @var \ProcessMaker\Nayra\Contracts\Bpmn\EventDefinitionInterface $terminate
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
     * If a token reaches a Terminate End Event, the entire Process is terminated.
     *
     * @param ExecutionInstanceInterface $executionInstance
     *
     * @return \ProcessMaker\Nayra\Bpmn\Collection
     */
    protected function evaluateConsumeTokens(ExecutionInstanceInterface $executionInstance)
    {
        $tokens = [];
        foreach($executionInstance->getTokens() as $token) {
            if ($this->assertCondition($token, $executionInstance)) {
                $this->eventDefinition->execute($this->eventDefinition, $this->owner, $executionInstance, $token);
                $tokens[]=$token;
            }
        }
        return new Collection($tokens);
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
