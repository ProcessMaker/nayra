<?php

namespace ProcessMaker\Nayra\Bpmn;

use ProcessMaker\Nayra\Contracts\Bpmn\ActivityInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\BoundaryEventInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\TokenInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\TransitionInterface;
use ProcessMaker\Nayra\Contracts\Engine\ExecutionInstanceInterface;

/**
 * Boundary Caught Transition.
 */
class BoundaryCaughtTransition implements TransitionInterface
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

        return $this->existsBoundaryFor($activity, $eventType, $eventDefinitionId);
    }

    /**
     * Check if activity has a boundary event for the given event definition.
     *
     * @param ActivityInterface $activity
     * @param string $eventType
     * @param string $eventDefinitionId
     *
     * @return bool
     */
    protected function existsBoundaryFor(ActivityInterface $activity, $eventType, $eventDefinitionId)
    {
        $catchException = $activity->getBoundaryEvents()->findFirst(function (BoundaryEventInterface $catch) use ($eventDefinitionId) {
            foreach ($catch->getEventDefinitions() as $eventDefinition) {
                if ($eventDefinition->getId() === $eventDefinitionId) {
                    return true;
                }
            }
        });

        return !empty($catchException);
    }
}
