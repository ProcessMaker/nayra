<?php

namespace ProcessMaker\Nayra\Bpmn;

use ProcessMaker\Nayra\Contracts\Bpmn\ActivityInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\StandardLoopCharacteristicsInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\TokenInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\TransitionInterface;
use ProcessMaker\Nayra\Contracts\Engine\ExecutionInstanceInterface;

/**
 * Transition rule that always pass the token.
 *
 * @package ProcessMaker\Nayra\Bpmn
 */
class SkipActivityTransition implements TransitionInterface
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
        if ($loop instanceof StandardLoopCharacteristicsInterface) {
            $testBefore = $loop->getTestBefore();
            $completed = $loop->isLoopCompleted($executionInstance, $token);
            // If loop is completed, then Skip Activity Transition is triggered
            return $testBefore && $completed;
        }
        return false;
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
}
