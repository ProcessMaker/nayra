<?php

namespace ProcessMaker\Nayra\Bpmn;

use ProcessMaker\Nayra\Contracts\Bpmn\CancelInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\TokenInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\TransitionInterface;
use ProcessMaker\Nayra\Contracts\Engine\ExecutionInstanceInterface;

/**
 * Uncaught Cancel Transition.
 */
class UncaughtCancelTransition extends BoundaryCaughtTransition implements TransitionInterface
{
    use TransitionTrait;

    /**
     * Condition required to transit the element.
     *
     * @param TokenInterface|null $token
     * @param ExecutionInstanceInterface|null $executionInstance
     *
     * @return bool
     */
    public function assertCondition(TokenInterface $token = null, ExecutionInstanceInterface $executionInstance = null)
    {
        $activity = $this->getOwner();
        $eventType = $token->getProperty(TokenInterface::BPMN_PROPERTY_EVENT_TYPE);
        $eventDefinitionId = $token->getProperty(TokenInterface::BPMN_PROPERTY_EVENT_DEFINITION_CAUGHT);
        $matchType = $eventType === CancelInterface::class || is_a($eventType, CancelInterface::class);

        return $matchType && !$this->existsBoundaryFor($activity, $eventType, $eventDefinitionId);
    }
}
