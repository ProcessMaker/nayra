<?php

namespace ProcessMaker\Nayra\Bpmn\Models;

use ProcessMaker\Nayra\Bpmn\EventDefinitionTrait;
use ProcessMaker\Nayra\Contracts\Bpmn\EventDefinitionInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\FlowNodeInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\ItemDefinitionInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\SignalEventDefinitionInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\SignalInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\TokenInterface;
use ProcessMaker\Nayra\Contracts\Engine\ExecutionInstanceInterface;

/**
 * SignalEventDefinition class
 *
 */
class SignalEventDefinition implements SignalEventDefinitionInterface
{
    use EventDefinitionTrait;

    /**
     * @var string $id
     */
    private $id;

    /**
     * Returns the element's id
     *
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Sets the element id
     *
     * @param mixed $value
     *
     * @return $this
     */
    public function setId($value)
    {
        $this->id = $value;
        return $this;
    }

    /**
     * Sets the signal used in the signal event definition
     *
     * @param SignalInterface $value
     */
    public function setPayload($value)
    {
        $this->setProperty(SignalEventDefinitionInterface::BPMN_PROPERTY_SIGNAL, $value);
    }

    /**
     * Returns the signal of the signal event definition
     * @return mixed
     */
    public function getPayload()
    {
        return $this->getProperty(SignalEventDefinitionInterface::BPMN_PROPERTY_SIGNAL);
    }

    /**
     * Assert the event definition rule for trigger the event.
     *
     * @param EventDefinitionInterface $event
     * @param FlowNodeInterface $target
     * @param ExecutionInstanceInterface|null $instance
     * @param TokenInterface|null $token
     *
     * @return boolean
     */
    public function assertsRule(EventDefinitionInterface $event, FlowNodeInterface $target, ExecutionInstanceInterface $instance = null, TokenInterface $token = null)
    {
        return true;
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
        if ($instance && get_class($event) === SignalEventDefinition::class && $event->getPayload()->getItem()) {
            $instanceData = $instance->getDataStore()->getData();
            $eventData = json_decode($event->getPayload()->getItem()->getProperty(ItemDefinitionInterface::BPMN_PROPERTY_STRUCTURE), true);
            $newData = array_merge($instanceData, $eventData);
            $instance->getDataStore()->setData($newData);
        }
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
        $targetPayloadId = $this->getPayload() ? $this->getPayload()->getId() : $this->getProperty(SignalEventDefinitionInterface::BPMN_PROPERTY_SIGNAL_REF);
        $sourcePayloadId = $eventDefinition->getPayload() ? $eventDefinition->getPayload()->getId() : $eventDefinition->getProperty(SignalEventDefinitionInterface::BPMN_PROPERTY_SIGNAL_REF);
        return $targetPayloadId && $sourcePayloadId && $targetPayloadId === $sourcePayloadId;
    }
}
