<?php

namespace ProcessMaker\Nayra\Bpmn\Models;

use ProcessMaker\Nayra\Bpmn\BaseTrait;
use ProcessMaker\Nayra\Bpmn\Collection;
use ProcessMaker\Nayra\Bpmn\SignalEventDefinition;
use ProcessMaker\Nayra\Contracts\Bpmn\CatchEventInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\CollaborationInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\CollectionInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\CorrelationKeyInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\EventDefinitionInterface;
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
     * @var MessageFlowInterface[] $messageFlows
     */
    private $messageFlows;

    /**
     * @var CorrelationKeyInterface[] $correlationKeys
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
        return $this->messageFlows;
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
        $this->messageFlows->push($messageFlow);
        return $this;
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
     * @param boolean $isClosed
     *
     * @return $this
     */
    public function setClosed($isClosed)
    {
        $this->isClosed = $isClosed;
        return $this;
    }

    /**
     * Sends a message
     *
     * @param EventDefinitionInterface $message
     * @param TokenInterface $token
     */
    public function send(EventDefinitionInterface $message, TokenInterface $token)
    {
        $isBroadcast = is_a($message, SignalEventDefinition::class);
        foreach ($this->subscribers as $subscriber) {
            $subscriberPayload = $subscriber['node']->getEventDefinitions()->item(0);
            foreach ($this->getInstancesFor($subscriber['node'], $message, $token) as $instance) {
                if (!$isBroadcast && $subscriber['key'] === $message->getId()
                     || ($isBroadcast && is_a($subscriberPayload, SignalEventDefinition::class))
                ) {
                    $subscriber['node']->execute($message, $instance);
                }
            }
            //for start events that doesn't have instances
            if (!$isBroadcast && $subscriber['key'] === $message->getId()
                || ($isBroadcast && is_a($subscriberPayload, SignalEventDefinition::class))
            ) {
                $subscriber['node']->execute($message, null);
            }
        }
    }

    /**
     * Get instances related to the catch event node.
     *
     * @param \ProcessMaker\Nayra\Contracts\Bpmn\CatchEventInterface $node
     * @param EventDefinitionInterface $message
     * @param \ProcessMaker\Nayra\Contracts\Bpmn\TokenInterface $token
     *
     * @return \ProcessMaker\Nayra\Engine\ExecutionInstance[]
     */
    private function getInstancesFor(CatchEventInterface $node, EventDefinitionInterface $message, TokenInterface $token)
    {
        return $node->getTargetInstances($message, $token);
    }

    /**
     * Sends a message with a delay in milliseconds
     *
     * @param EventDefinitionInterface $message
     * @param $delay
     */
    public function delay(EventDefinitionInterface $message, $delay)
    {
        $initTime = time();
        if ($delay + $initTime <= time()) {
            $this->send($message, null);
        }
    }

    /**
     * Subscribes an element to the collaboration so that it can listen the messages sent
     *
     * @param MessageListenerInterface $node
     * @param string $messageId
     * @internal param string $id
     * @internal param MessageInterface $message
     *
     * @return mixed
     */
    public function subscribe(MessageListenerInterface $node, $messageId)
    {
        $this->subscribers [] = [
            'node' => $node,
            'key' => $messageId
        ];
    }

    /**
     * Unsubscribes an object to the collaboration, so that it won't listen to the messages sent
     *
     * @param MessageListenerInterface $node
     * @param string $messageId
     *
     * @internal param string $id
     * @internal param MessageInterface $message
     */
    public function unsubscribe(MessageListenerInterface $node, $messageId)
    {
        $this->subscribers = array_filter($this->subscribers,
            function ($e) use ($messageId) {
                return $e['key'] !== $messageId;
            });
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
        $this->messageFlows = $messageFlows;
        return $this;
    }
}
