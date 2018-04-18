<?php

namespace ProcessMaker\Nayra\Contracts\Bpmn;

/**
 * A collaboration is a collection of participants and the messages they exchange.
 *
 * @package ProcessMaker\Nayra\Contracts\Bpmn
 */
interface CollaborationInterface extends EntityInterface
{
    /**
     * Get a boolean value specifying whether Message Flows not modeled in the
     * Collaboration can occur when the Collaboration is carried out.
     *
     * @return bool
     */
    public function isClosed();

    /**
     * Set a boolean value specifying whether Message Flows not modeled in the
     * Collaboration can occur when the Collaboration is carried out.
     *
     * @param bool $isClosed
     *
     * @return bool
     */
    //public function setClosed($isClosed);

    /**
     * @return ParticipantInterface[]
     */
    public function getParticipants();

    //public function setParticipants(CollectionInterface $participants);

    /**
     * @return ConversationInterface[]
     */
    public function getConversations();

    //public function setConversations(CollectionInterface $conversationNode);

    /**
     * @return CorrelationKeyInterface[]
     */
    public function getCorrelationKeys();

    //public function setCorrelationKeys(CollectionInterface $correlationKeys);

    /**
     * @return MessageFlowInterface[]
     */
    public function getMessageFlows();

    public function addMessageFlow(MessageFlowInterface $messageFlow);

    public function setMessageFlows(CollectionInterface $messageFlows);

    /**
     * Sends a message
     *
     * @param MessageInterface $message
     *
     * @return mixed
     */
    public function send(MessageEventDefinitionInterface $message);

    /**
     * Sends a message with a delay in miliseconds
     *
     * @param MessageInterface $message
     * @param $delay
     *
     * @return mixed
     */
    public function delay(MessageEventDefinitionInterface $message, $delay);

    /**
     * Subscribes an element to the collaboration so that it can listen the messages sent
     *
     * @param MessageListenerInterface $element
     * @param string $messageId
     * @return mixed
     * @internal param string $id
     * @internal param MessageInterface $message
     */
    public function subscribe(MessageListenerInterface $element, string $messageId);

    /**
     * Unsuscribes an object to the collaboration, so that it won't listen to the messages sent
     *
     * @param MessageListenerInterface $element
     * @param string $messageId
     * @return mixed
     * @internal param string $id
     * @internal param MessageInterface $message
     */
    public function unsubscribe(MessageListenerInterface $element, string $messageId);
}
