<?php

namespace ProcessMaker\Nayra\Bpmn;

use ProcessMaker\Nayra\Contracts\Bpmn\TokenInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\TransitionInterface;
use ProcessMaker\Nayra\Contracts\Engine\ExecutionInstanceInterface;

/**
 * Transition rule that always pass the token.
 */
class ParallelOutputTransition extends Transition implements TransitionInterface
{
    use ForceGatewayTransitionTrait;

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
        // If debug mode is enabled, the transition is triggered only if it is selected
        if ($executionInstance && $this->shouldDebugTriggerThisTransition($executionInstance)) {
            return true;
        }
        // If debug mode is enabled, the transition is not triggered if it is not selected
        if ($executionInstance && $this->shouldDebugSkipThisTransition($executionInstance)) {
            return false;
        }

        // By default the transition is triggered
        return true;
    }
}
