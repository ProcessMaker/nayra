<?php

namespace ProcessMaker\Test\Models;

use ProcessMaker\Nayra\Bpmn\ActivitySubProcessTrait;
use ProcessMaker\Nayra\Bpmn\Events\ActivityActivatedEvent;
use ProcessMaker\Nayra\Bpmn\Events\ActivityClosedEvent;
use ProcessMaker\Nayra\Bpmn\Events\ActivityCompletedEvent;
use ProcessMaker\Nayra\Contracts\Bpmn\ActivityInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\CallActivityInterface;

/**
 * This activity will raise an exception when executed.
 */
class CallActivity implements CallActivityInterface
{
    use ActivitySubProcessTrait;

    /**
     * Array map of custom event classes for the bpmn element.
     *
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

    /**
     * Get the called element by the activity.
     *
     * @return \ProcessMaker\Nayra\Contracts\Bpmn\CallableElementInterface
     */
    public function getCalledElement()
    {
        return $this->getProperty(CallActivityInterface::BPMN_PROPERTY_CALLED_ELEMENT);
    }

    /**
     * Set the called element by the activity.
     *
     * @param \ProcessMaker\Nayra\Contracts\Bpmn\CallableElementInterface|string $callableElement
     *
     * @return $this
     */
    public function setCalledElement($callableElement)
    {
        $this->setProperty(CallActivityInterface::BPMN_PROPERTY_CALLED_ELEMENT, $callableElement);

        return $this;
    }
}
