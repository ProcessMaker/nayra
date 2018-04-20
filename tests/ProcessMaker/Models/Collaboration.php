<?php

namespace ProcessMaker\Models;

use ProcessMaker\Nayra\Bpmn\BaseTrait;
use ProcessMaker\Nayra\Bpmn\Collection;
use ProcessMaker\Nayra\Contracts\Bpmn\MessageListenerInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\CollaborationInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\CollectionInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\MessageEventDefinitionInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\MessageFlowInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\MessageInterface;

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

    public function addMessageFlow(MessageFlowInterface $messageFlow)
    {
        $this->messageFlows->push($messageFlow);
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

    private $subscribers = [];

    public function send(MessageEventDefinitionInterface $message)
    {
        foreach ($this->subscribers as $subscriber) {
            if ($subscriber['key'] === $message->getId()) {
                $subscriber['node']->execute($message);
            }
        }
    }

    public function delay(MessageEventDefinitionInterface $message, $delay)
    {
        $initTime = time();
        if ($delay + $initTime <= time()) {
            $this->send($message);
        }
    }

    public function subscribe(MessageListenerInterface $node, string $messageId)
    {
        $this->subscribers [] = [
            'node' => $node,
            'key' => $messageId
        ];
    }

    public function unsubscribe(MessageListenerInterface $node, string $messageId)
    {
        $this->subscribers = array_filter($this->subscribers,
            function ($e) use ($messageId) {
                return $e['key'] !== $messageId;
            });
    }

    public function setMessageFlows(CollectionInterface $messageFlows)
    {
        // TODO: Implement setMessageFlows() method.
    }
}
