<?php

namespace ProcessMaker\Models;

use ProcessMaker\Nayra\Bpmn\ActivitySubProcessTrait;
use ProcessMaker\Nayra\Contracts\Bpmn\ActivityInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\CallActivityInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\ErrorEventDefinitionInterface;
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
     * Configure the activity to go to a FAILING status when activated.
     *
     */
    public function initActivity()
    {
        $this->attachEvent(
            ActivityInterface::EVENT_ACTIVITY_ACTIVATED,
            function ($self, TokenInterface $token) {
                $instance = $this->getCalledElement()->call();
                $this->getCalledElement()->attachEvent(
                    ProcessInterface::EVENT_PROCESS_COMPLETED,
                    function ($self, $closedInstance) use($token, $instance) {
                        if ($closedInstance === $instance) {
                            $token->setStatus(ActivityInterface::TOKEN_STATE_COMPLETED);
                        }
                    }
                );
                $this->getCalledElement()->attachEvent(
                    ErrorEventDefinitionInterface::EVENT_THROW_EVENT_DEFINITION,
                    function ($element, $innerToken, $errorEvent) use($token, $instance) {
                        if ($innerToken->getInstance() === $instance) {
                            $token->setStatus(ActivityInterface::TOKEN_STATE_FAILING);
                        }
                    }
                );
            }
        );
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
        return $this->getProperty(CallActivityInterface::BPMN_PROPERTY_CALLED_ELEMENT);
    }

    public function setCalledElement(ProcessInterface $callableElement)
    {
        $this->setProperty(CallActivityInterface::BPMN_PROPERTY_CALLED_ELEMENT, $callableElement);
        return $this;
    }
}
