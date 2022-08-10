<?php

namespace ProcessMaker\Nayra\Bpmn\Models;

use ProcessMaker\Nayra\Bpmn\EventDefinitionTrait;
use ProcessMaker\Nayra\Contracts\Bpmn\ErrorEventDefinitionInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\ErrorInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\EventDefinitionInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\FlowNodeInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\TokenInterface;
use ProcessMaker\Nayra\Contracts\Engine\ExecutionInstanceInterface;

/**
 * ErrorEventDefinition class
 */
class ErrorEventDefinition implements ErrorEventDefinitionInterface
{
    use EventDefinitionTrait;

    /**
     * Assert the event definition rule to trigger the event.
     *
     * @param EventDefinitionInterface $event
     * @param FlowNodeInterface $target
     * @param ExecutionInstanceInterface|null $instance
     * @param TokenInterface|null $token
     *
     * @return bool
     */
    public function assertsRule(EventDefinitionInterface $event, FlowNodeInterface $target, ExecutionInstanceInterface $instance = null, TokenInterface $token = null)
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
     * Set the error of the event definition.
     *
     * @param ErrorInterface $error
     *
     * @return $this
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

    /**
     * Implement the event definition behavior when an event is triggered.
     *
     * @param EventDefinitionInterface $event
     * @param FlowNodeInterface $target
     * @param ExecutionInstanceInterface|null $instance
     * @param TokenInterface|null $token
     *
     * @return $this
     */
    public function execute(EventDefinitionInterface $event, FlowNodeInterface $target, ExecutionInstanceInterface $instance = null, TokenInterface $token = null)
    {
        return $this;
    }

    /**
     * Check if the $eventDefinition should be catch
     *
     * @param EventDefinitionInterface $eventDefinition
     *
     * @return bool
     */
    public function shouldCatchEventDefinition(EventDefinitionInterface $eventDefinition)
    {
        $targetPayloadId = $this->getPayload() ? $this->getPayload()->getId() : $this->getProperty(ErrorEventDefinitionInterface::BPMN_PROPERTY_ERROR_REF);
        $sourcePayloadId = $eventDefinition->getPayload() ? $eventDefinition->getPayload()->getId() : $eventDefinition->getProperty(ErrorEventDefinitionInterface::BPMN_PROPERTY_ERROR_REF);

        return !$targetPayloadId || ($targetPayloadId && $sourcePayloadId && $targetPayloadId === $sourcePayloadId);
    }
}
