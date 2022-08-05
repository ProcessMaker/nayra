<?php

namespace ProcessMaker\Nayra\Bpmn;

use ProcessMaker\Nayra\Bpmn\Models\ErrorEventDefinition;
use ProcessMaker\Nayra\Contracts\Bpmn\ActivityInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\BoundaryEventInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\ErrorInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\TokenInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\TransitionInterface;
use ProcessMaker\Nayra\Contracts\Engine\ExecutionInstanceInterface;

/**
 * Boundary Caught Transition.
 */
class BoundaryExceptionTransition implements TransitionInterface
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

        return $this->existsBoundaryErrorFor($activity, $eventType, null);
    }

    /**
     * Check if activity has a boundary event for the given event definition.
     *
     * @param ActivityInterface $activity
     * @param string $eventType
     *
     * @return bool
     */
    protected function existsBoundaryErrorFor(ActivityInterface $activity, $eventType)
    {
        $catchException = $activity->getBoundaryEvents()->findFirst(function (BoundaryEventInterface $catch) use ($eventType) {
            foreach ($catch->getEventDefinitions() as $eventDefinition) {
                if ($eventDefinition instanceof ErrorEventDefinition) {
                    $matchType = $eventType === ErrorInterface::class || is_a($eventType, ErrorInterface::class);

                    return $matchType && $catch->getCancelActivity();
                }
            }
        });

        return ! empty($catchException);
    }
}
