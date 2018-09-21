<?php

namespace ProcessMaker\Nayra\Bpmn\Models;

use ProcessMaker\Nayra\Bpmn\BaseTrait;
use ProcessMaker\Nayra\Bpmn\Collection;
use ProcessMaker\Nayra\Bpmn\Models\SignalEventDefinition;
use ProcessMaker\Nayra\Contracts\Bpmn\CatchEventInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\CollaborationInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\CollectionInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\CorrelationKeyInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\EventDefinitionInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\MessageFlowInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\MessageListenerInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\ProcessInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\StartEventInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\TokenInterface;

/**
 * Implementation of Collaboration element.
 *
 */
class Collaboration implements CollaborationInterface
{

    use BaseTrait;

    private $subscribers = [];

    /**
     * @var boolean $isClosed
     */
    private $isClosed;

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

    /**
     * Initialize the collaboration element.
     *
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
        foreach ($this->subscribers as $subscriber) {
            $this->subscriberReceiveEvent($message, $token, $subscriber);
        }
    }

    /**
     * A subscriber receive a message within a collaboration.
     *
     * @param EventDefinitionInterface $message
     * @param TokenInterface $token
     * @param array $subscriber
     */
    private function subscriberReceiveEvent(EventDefinitionInterface $message, TokenInterface $token, array $subscriber)
    {
        $isBroadcast = $message instanceof SignalEventDefinition;
        foreach ($subscriber['node']->getEventDefinitions() as $subscriberPayload) {
            $match = !$isBroadcast && $subscriber['key'] === $message->getId()
                || ($isBroadcast && $subscriberPayload instanceof SignalEventDefinition);
            if ($match && $subscriber['node'] instanceof StartEventInterface) {
                $this->startEventReceiveMessage($subscriber['node'], $message, $token);
            } elseif ($match) {
                $this->catchEventReceiveMessage($subscriber['node'], $message, $token);
            }
        }
    }

    /**
     * A Start Event receive a message within a collaboration.
     *
     * @param StartEventInterface $startEvent
     * @param EventDefinitionInterface $message
     * @param TokenInterface $token
     */
    private function startEventReceiveMessage(StartEventInterface $startEvent, EventDefinitionInterface $message, TokenInterface $token)
    {
        $process = $startEvent->getOwnerProcess();
        $dataStorage = $process->getRepository()->createDataStore();
        $instance = $process->getEngine()->createExecutionInstance($process, $dataStorage);
        $instanceRepository = $process->getRepository()->createExecutionInstanceRepository();
        $participant = $this->getParticipantFor($process);
        $sourceInstance = $token->getInstance();
        $sourceParticipant = $this->getParticipantFor($sourceInstance->getProcess());
        $instanceRepository->persistInstanceCollaboration($instance, $participant, $sourceInstance, $sourceParticipant);
        $startEvent->execute($message, $instance, $token);
    }

    /**
     * A catch event receive a message within a collaboration.
     *
     * @param CatchEventInterface $catchEvent
     * @param EventDefinitionInterface $message
     * @param TokenInterface $token
     */
    private function catchEventReceiveMessage(CatchEventInterface $catchEvent, EventDefinitionInterface $message, TokenInterface $token)
    {
        foreach ($this->getInstancesFor($catchEvent, $message, $token) as $instance) {
            $catchEvent->execute($message, $instance, $token);
        }
    }

    /**
     * Get the participant of a specific $process.
     *
     * @param ProcessInterface $process
     *
     * @return \ProcessMaker\Nayra\Contracts\Bpmn\ParticipantInterface
     */
    private function getParticipantFor(ProcessInterface $process)
    {
        $participantFor = null;
        foreach ($this->getParticipants() as $participant) {
            if ($participant->getProcess()->getId() === $process->getId()) {
                $participantFor = $participant;
                break;
            }
        }
        return $participantFor;
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
        return $this->setProperty(static::BPMN_PROPERTY_MESSAGE_FLOWS, $messageFlows);
    }
}
