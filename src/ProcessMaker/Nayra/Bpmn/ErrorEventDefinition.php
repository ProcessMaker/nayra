<?php

namespace ProcessMaker\Nayra\Bpmn;

use ProcessMaker\Nayra\Contracts\Bpmn\ErrorEventDefinitionInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\ErrorInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\EventDefinitionInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\FlowNodeInterface;
use ProcessMaker\Nayra\Contracts\Engine\ExecutionInstanceInterface;

class ErrorEventDefinition implements ErrorEventDefinitionInterface
{

    use BaseTrait;

    /**
     * Assert the event definition rule to trigger the event.
     *
     * @param EventDefinitionInterface $event
     * @param FlowNodeInterface $target
     * @param ExecutionInstanceInterface $instance
     *
     * @return boolean
     */
    public function assertsRule(EventDefinitionInterface $event, FlowNodeInterface $target, ExecutionInstanceInterface $instance = null)
    {
        return true;
    }

    /**
     * Get the error of the event definition.
     *
     * @return ErrorInterface
     */
    public function getError()
    {
        return $this->getProperty(ErrorEventDefinitionInterface::BPMN_PROPERTY_ERROR);
    }

    /**
     * Get the error of the event definition.
     *
     * @return ErrorInterface
     */
    public function setError(ErrorInterface $error)
    {
        $this->setProperty(ErrorEventDefinitionInterface::BPMN_PROPERTY_ERROR, $error);
        return $this;
    }

    /**
     * Returns the event definition payload (message, signal, etc.)
     *
     * @return mixed
     */
    public function getPayload()
    {
        return $this->getError();
    }
}
