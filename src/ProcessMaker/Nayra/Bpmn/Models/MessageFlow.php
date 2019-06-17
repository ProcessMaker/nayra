<?php

namespace ProcessMaker\Nayra\Bpmn\Models;

use ProcessMaker\Nayra\Bpmn\MessageFlowTrait;
use ProcessMaker\Nayra\Contracts\Bpmn\CatchEventInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\CollaborationInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\MessageFlowInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\MessageInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\ThrowEventInterface;

/**
 * End event implementation.
 *
 * @package ProcessMaker\Models
 */
class MessageFlow implements MessageFlowInterface
{
    use MessageFlowTrait;

    private $message;

    /**
     * Get message
     *
     * @return MessageInterface
     */
    public function getMessage()
    {
        return $this->message;
    }

    /**
     * Set message.
     *
     * @param MessageInterface $value
     */
    public function setMessage(MessageInterface $value)
    {
        $this->message = $value;
    }

    /**
     * Source of the message.
     *
     * @return \ProcessMaker\Nayra\Contracts\Bpmn\FlowElementInterface
     */
    public function getSource()
    {
        return $this->getProperty(static::BPMN_PROPERTY_SOURCE);
    }

    /**
     * Target of the message.
     *
     * @return \ProcessMaker\Nayra\Contracts\Bpmn\FlowElementInterface
     */
    public function getTarget()
    {
        return $this->getProperty(static::BPMN_PROPERTY_TARGET);
    }

    /**
     * Set the source.
     *
     * @param ThrowEventInterface $source
     *
     * @return $this
     */
    public function setSource(ThrowEventInterface $source)
    {
        return $this->setProperty(static::BPMN_PROPERTY_SOURCE, $source);
    }

    /**
     * Set the target.
     *
     * @param CatchEventInterface $target
     *
     * @return $this
     */
    public function setTarget(CatchEventInterface $target)
    {
        $eventDef = $this->getSource()->getEventDefinitions()->item(0);
        $eventDef->getPayload()->setMessageFlow($this);
        return $this->setProperty(static::BPMN_PROPERTY_TARGET, $target);
    }

    /**
     * Sets the collaboration of this element
     *
     * @param CollaborationInterface $collaboration
     *
     * @return $this
     */
    public function setCollaboration(CollaborationInterface $collaboration)
    {
        return $this->setProperty(static::BPMN_PROPERTY_COLLABORATION, $collaboration);
    }

    /**
     * Returns the collaboration of this element
     *
     * @return CollaborationInterface
     */
    public function getCollaboration()
    {
        return $this->getProperty(static::BPMN_PROPERTY_COLLABORATION);
    }
}
