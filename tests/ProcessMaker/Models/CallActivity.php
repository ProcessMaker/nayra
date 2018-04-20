<?php

namespace ProcessMaker\Models;

use ProcessMaker\Nayra\Bpmn\ActivitySubProcessTrait;
use ProcessMaker\Nayra\Contracts\Bpmn\ActivityInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\CallActivityInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\ProcessInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\TokenInterface;

/**
 * This activity will raise an exception when executed.
 *
 */
class CallActivity implements CallActivityInterface
{

    use ActivitySubProcessTrait,
        LocalFlowNodeTrait,
        LocalProcessTrait,
        LocalPropertiesTrait;
    /**
     * @var \ProcessMaker\Nayra\Contracts\Bpmn\ProcessInterface $process
     */
    private $calledElement;

    /**
     * Configure the activity to go to a FAILING status when activated.
     *
     */
    public function initActivity()
    {
        $this->attachEvent(ActivityInterface::EVENT_ACTIVITY_ACTIVATED,
                           function ($self, TokenInterface $token) {
        });
    }

    /**
     * Array map of custom event classes for the bpmn element.
     *
     * @return array
     */
    protected function getBpmnEventClasses()
    {
        return [
            ActivityInterface::EVENT_ACTIVITY_ACTIVATED => ActivityActivatedEvent::class,
        ];
    }

    public function getCalledElement()
    {
        return $this->calledElement;
    }

    public function setCalledElement(ProcessInterface $callableElement)
    {
        $this->calledElement = $callableElement;
        return $this;
    }
}
