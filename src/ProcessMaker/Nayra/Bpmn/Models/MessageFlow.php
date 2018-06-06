<?php

namespace ProcessMaker\Nayra\Bpmn\Models;

use ProcessMaker\Nayra\Bpmn\MessageFlowTrait;
use ProcessMaker\Nayra\Contracts\Bpmn\CatchEventInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\CollaborationInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\FlowNodeInterface;
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
    private $source;
    private $target;
    private $collaboration;

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
        return $this->source;
    }

    /**
     * Target of the message.
     *
     * @return \ProcessMaker\Nayra\Contracts\Bpmn\FlowElementInterface
     */
    public function getTarget()
    {
        return $this->target;
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
        $this->source = $source;
        return $this;
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
        $this->getCollaboration()->unsubscribe($target, $eventDef->getId());
        $this->getCollaboration()->subscribe($target, $eventDef->getId());
        $this->target = $target;
        return $this;
    }


    /**
     * Sets the collaboration to which this element pertains
     * @param CollaborationInterface $collaboration
     */
    public function setCollaboration(CollaborationInterface $collaboration)
    {
        $this->collaboration = $collaboration;
    }

    /**
     * Returns the collaboration to which this element pertains
     *
     * @return CollaborationInterface
     */
    public function getCollaboration()
    {
        return $this->collaboration;
    }
}
