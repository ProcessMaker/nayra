<?php

namespace ProcessMaker\Nayra\Bpmn;

use ProcessMaker\Nayra\Bpmn\Collection;
use ProcessMaker\Nayra\Bpmn\TransitionTrait;
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
     * @var \ProcessMaker\Nayra\Bpmn\TerminateEventDefinition $terminate
     */
    private $terminate;

    /**
     * Condition required to terminate the token.
     *
     * @param TokenInterface $token
     *
     * @return bool
     */
    public function assertCondition(TokenInterface $token = null, ExecutionInstanceInterface $executionInstance)
    {
        return $this->terminate->assertsRule($this->terminate, $this->owner, $executionInstance);
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
                $tokens[]=$token;
            }
        }
        return new Collection($tokens);
    }

    /**
     * Set the terminate event definition.
     *
     * @param \ProcessMaker\Nayra\Bpmn\TerminateEventDefinition $terminate
     */
    public function setTerminateEventDefinition(TerminateEventDefinition $terminate)
    {
        $this->terminate = $terminate;
    }
}
