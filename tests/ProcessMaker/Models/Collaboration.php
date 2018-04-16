<?php

namespace ProcessMaker\Models;

use ProcessMaker\Nayra\Bpmn\BaseTrait;
use ProcessMaker\Nayra\Bpmn\Collection;
use ProcessMaker\Nayra\Contracts\Bpmn\CollaborationInterface;

class Collaboration implements CollaborationInterface
{

    use BaseTrait;

    /**
     * @var boolean $isClosed
     */
    private $isClosed;

    /**
     * @var ParticipantInterface[] $participants
     */
    private $participants;

    /**
     * @var TODO_MessageFlowInterface[] $messageFlows
     */
    private $messageFlows;

    /**
     * @var TODO_ArtifactInterface[] $artifacts
     */
    private $artifacts;

    /**
     * @var TODO_ConversationNodeInterface[] $conversationNodes
     */
    private $conversationNodes;

    /**
     * @var TODO_ConversationAssociationInterface[] $conversationAssociations
     */
    private $conversationAssociations;

    /**
     * @var TODO_ParticipantAssociationInterface[] $participantAssociations
     */
    private $participantAssociations;

    /**
     * @var TODO_MessageFlowAssociationInterface[] $messageFlowAssociations
     */
    private $messageFlowAssociations;

    /**
     * @var TODO_CorrelationKeyInterface[] $correlationKeys
     */
    private $correlationKeys;

    /**
     * @var TODO_choreographyInterface[] $choreographies
     */
    private $choreographies;

    /**
     * @var TODO_ConversationLinkInterface[] $conversationLinks
     */
    private $conversationLinks;

    protected function initCollaboration()
    {
        $this->participants = new Collection;
        $this->artifacts = new Collection;
        $this->choreographies = new Collection;
        $this->conversationAssociations = new Collection;
        $this->conversationLinks = new Collection;
        $this->conversationNodes = new Collection;
        $this->correlationKeys = new Collection;
        $this->messageFlowAssociations = new Collection;
        $this->messageFlows = new Collection;
        $this->participantAssociations = new Collection;
        $this->participants = new Collection;
    }

    public function getConversations()
    {
        return $this->conversationNodes;
    }

    public function getCorrelationKeys()
    {
        return $this->correlationKeys;
    }

    public function getMessageFlows()
    {
        return $this->messageFlows;
    }

    public function getParticipants()
    {
        return $this->participants;
    }

    public function getProperties()
    {

    }

    public function getProperty($name, $default = null)
    {
        
    }

    public function isClosed()
    {
        return $this->isClosed;
    }

    public function setClosed($isClosed)
    {
        $this->isClosed = $isClosed;
        return $this;
    }

    public function setFactory(\ProcessMaker\Nayra\Contracts\Repositories\RepositoryFactoryInterface $factory)
    {

    }

    public function setProperties(array $properties)
    {
        
    }

    public function setProperty($name, $value)
    {

    }
}
