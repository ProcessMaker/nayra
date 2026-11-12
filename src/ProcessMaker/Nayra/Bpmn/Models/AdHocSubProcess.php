<?php

namespace ProcessMaker\Nayra\Bpmn\Models;

use ProcessMaker\Nayra\Bpmn\EmbeddedSubProcessTrait;
use ProcessMaker\Nayra\Bpmn\Events\ActivityActivatedEvent;
use ProcessMaker\Nayra\Bpmn\Events\ActivityClosedEvent;
use ProcessMaker\Nayra\Bpmn\Events\ActivityCompletedEvent;
use ProcessMaker\Nayra\Contracts\Bpmn\ActivityInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\AdHocSubProcessInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\FormalExpressionInterface;

/**
 * BPMN Ad-Hoc SubProcess.
 */
class AdHocSubProcess implements AdHocSubProcessInterface
{
    use EmbeddedSubProcessTrait;

    /**
     * Initialize ad-hoc defaults.
     */
    protected function initAdHocSubProcess()
    {
        $this->setOrdering(AdHocSubProcessInterface::ORDERING_PARALLEL);
    }

    /**
     * @return FormalExpressionInterface|null
     */
    public function getCompletionCondition()
    {
        return $this->getProperty(AdHocSubProcessInterface::BPMN_PROPERTY_COMPLETION_CONDITION);
    }

    /**
     * @param FormalExpressionInterface|null $condition
     *
     * @return $this
     */
    public function setCompletionCondition(FormalExpressionInterface $condition = null)
    {
        return $this->setProperty(AdHocSubProcessInterface::BPMN_PROPERTY_COMPLETION_CONDITION, $condition);
    }

    /**
     * @return string
     */
    public function getOrdering()
    {
        return $this->getProperty(
            AdHocSubProcessInterface::BPMN_PROPERTY_ORDERING,
            AdHocSubProcessInterface::ORDERING_PARALLEL
        );
    }

    /**
     * @param string $ordering
     *
     * @return $this
     */
    public function setOrdering($ordering)
    {
        return $this->setProperty(AdHocSubProcessInterface::BPMN_PROPERTY_ORDERING, $ordering);
    }

    /**
     * @return array
     */
    protected function getBpmnEventClasses()
    {
        return [
            ActivityInterface::EVENT_ACTIVITY_ACTIVATED => ActivityActivatedEvent::class,
            ActivityInterface::EVENT_ACTIVITY_COMPLETED => ActivityCompletedEvent::class,
            ActivityInterface::EVENT_ACTIVITY_CLOSED => ActivityClosedEvent::class,
        ];
    }
}
