<?php

namespace ProcessMaker\Models;

use ProcessMaker\Nayra\Bpmn\BaseTrait;
use ProcessMaker\Nayra\Bpmn\Collection;
use ProcessMaker\Nayra\Contracts\Bpmn\CatchEventInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\CollaborationInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\CollectionInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\MessageEventDefinitionInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\MessageFlowInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\MessageListenerInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\TokenInterface;

class Collaboration implements CollaborationInterface
{

    use BaseTrait;

    private $subscribers = [];

    /**
     * @var boolean $isClosed
     */
    private $isClosed;

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
        if ($this->getProperty(CollaborationInterface::BPMN_PROPERTY_PARTICIPANT)===null) {
            $this->setProperty(CollaborationInterface::BPMN_PROPERTY_PARTICIPANT, new Collection);
        }
        return $this->getProperty(CollaborationInterface::BPMN_PROPERTY_PARTICIPANT);
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

    public function send(MessageEventDefinitionInterface $message, TokenInterface $token)
    {
        foreach ($this->subscribers as $subscriber) {
            if ($subscriber['key'] === $message->getId()) {
                foreach ($this->getInstancesFor($subscriber['node'], $message, $token) as $instance) {
                    $subscriber['node']->execute($message, $instance);
                }
            }
        }
    }

    /**
     *
     * @param MessageEventDefinitionInterface $message
     * @param type $node
     *
     * @return \ProcessMaker\Nayra\Engine\ExecutionInstance[]
     */
    private function getInstancesFor(CatchEventInterface $node, MessageEventDefinitionInterface $message, TokenInterface $token)
    {
        return $node->getTargetInstances($message, $token);
    }

    public function delay(MessageEventDefinitionInterface $message, $delay)
    {
        $initTime = time();
        if ($delay + $initTime <= time()) {
            $this->send($message, null);
        }
    }

    public function subscribe(MessageListenerInterface $node, $messageId)
    {
        $this->subscribers [] = [
            'node' => $node,
            'key' => $messageId
        ];
    }

    public function unsubscribe(MessageListenerInterface $node, $messageId)
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
