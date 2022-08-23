<?php

namespace ProcessMaker\Nayra\Bpmn\Models;

use ProcessMaker\Nayra\Bpmn\BaseTrait;
use ProcessMaker\Nayra\Bpmn\Collection;
use ProcessMaker\Nayra\Contracts\Bpmn\CollaborationInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\CollectionInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\CorrelationKeyInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\MessageFlowInterface;

/**
 * Implementation of Collaboration element.
 */
class Collaboration implements CollaborationInterface
{
    use BaseTrait;

    /**
     * @var bool
     */
    private $isClosed;

    /**
     * @var CorrelationKeyInterface[]
     */
    private $correlationKeys;

    /** @var Collection */
    private $artifacts;

    /** @var Collection */
    private $choreographies;

    /** @var Collection */
    private $conversationAssociations;

    /** @var Collection */
    private $conversationLinks;

    /** @var Collection */
    private $conversationNodes;

    /** @var Collection */
    private $messageFlowAssociations;

    /** @var Collection */
    private $participantAssociations;

    /**
     * Initialize the collaboration element.
     */
    protected function initCollaboration()
    {
        $this->artifacts = new Collection;
        $this->choreographies = new Collection;
        $this->conversationAssociations = new Collection;
        $this->conversationLinks = new Collection;
        $this->conversationNodes = new Collection;
        $this->correlationKeys = new Collection;
        $this->messageFlowAssociations = new Collection;
        $this->participantAssociations = new Collection;
        $this->setMessageFlows(new Collection);
        $this->setProperty(CollaborationInterface::BPMN_PROPERTY_PARTICIPANT, new Collection);
    }

    /**
     * Get correlation keys.
     *
     * @return CorrelationKeyInterface[]
     */
    public function getCorrelationKeys()
    {
        return $this->correlationKeys;
    }

    /**
     * Get message flows.
     *
     * @return MessageFlowInterface[]
     */
    public function getMessageFlows()
    {
        return $this->getProperty(static::BPMN_PROPERTY_MESSAGE_FLOWS);
    }

    /**
     * Add a message flow.
     *
     * @param MessageFlowInterface $messageFlow
     *
     * @return $this
     */
    public function addMessageFlow(MessageFlowInterface $messageFlow)
    {
        return $this->addProperty(static::BPMN_PROPERTY_MESSAGE_FLOWS, $messageFlow);
    }

    /**
     * Get participants.
     *
     * @return \ProcessMaker\Nayra\Contracts\Bpmn\ParticipantInterface[]
     */
    public function getParticipants()
    {
        return $this->getProperty(CollaborationInterface::BPMN_PROPERTY_PARTICIPANT);
    }

    /**
     * Get a boolean value specifying whether Message Flows not modeled in the
     * Collaboration can occur when the Collaboration is carried out.
     *
     * @return bool
     */
    public function isClosed()
    {
        return $this->isClosed;
    }

    /**
     * Set if the collaboration is closed.
     *
     * @param bool $isClosed
     *
     * @return $this
     */
    public function setClosed($isClosed)
    {
        $this->isClosed = $isClosed;

        return $this;
    }

    /**
     * Set message flows collection.
     *
     * @param CollectionInterface $messageFlows
     *
     * @return $this
     */
    public function setMessageFlows(CollectionInterface $messageFlows)
    {
        return $this->setProperty(static::BPMN_PROPERTY_MESSAGE_FLOWS, $messageFlows);
    }
}
