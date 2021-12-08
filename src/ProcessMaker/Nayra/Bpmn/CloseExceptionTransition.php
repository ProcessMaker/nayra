<?php

namespace ProcessMaker\Nayra\Bpmn;

use ProcessMaker\Nayra\Contracts\Bpmn\ActivityInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\TokenInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\TransitionInterface;
use ProcessMaker\Nayra\Contracts\Engine\ExecutionInstanceInterface;

/**
 * Transition rule to close an activity in FAILING state.
 *
 * @package ProcessMaker\Nayra\Bpmn
 */
class CloseExceptionTransition implements TransitionInterface
{
    use TransitionTrait;

    /**
     * Initialize transition.
     *
     */
    protected function initActivityTransition()
    {
        $this->setPreserveToken(true);
    }

    /**
     * Condition required to transit an activity in FAILING state.
     *
     * @param TokenInterface|null $token
     * @param ExecutionInstanceInterface|null $executionInstance
     *
     * @return bool
     */
    public function assertCondition(TokenInterface $token = null, ExecutionInstanceInterface $executionInstance = null)
    {
        return $token->getStatus() === ActivityInterface::TOKEN_STATE_CLOSED;
    }
}
