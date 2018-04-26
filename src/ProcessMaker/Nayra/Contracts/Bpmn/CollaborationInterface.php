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
     * @return ParticipantInterface[]
     */
    public function getParticipants();

    /**
     * @return ConversationInterface[]
     */
    public function getConversations();

    /**
     * @return CorrelationKeyInterface[]
     */
    public function getCorrelationKeys();

    /**
     * @return MessageFlowInterface[]
     */
    public function getMessageFlows();

    public function addMessageFlow(MessageFlowInterface $messageFlow);

    public function setMessageFlows(CollectionInterface $messageFlows);

    /**
     * Sends a message
     *
     * @param mixed $message
     *
     */
    public function send($message);

    /**
     * Sends a message with a delay in miliseconds
     *
     * @param mixed $message
     * @param $delay
     *
     * @return mixed
     */
    public function delay($message, $delay);

    /**
     * Subscribes an element to the collaboration so that it can listen the messages sent
     *
     * @param MessageListenerInterface $element
     * @param string $messageId
     * @internal param string $id
     * @internal param MessageInterface $message
     *
     * @return mixed
     */
    public function subscribe(MessageListenerInterface $element, $messageId);

    /**
     * Unsuscribes an object to the collaboration, so that it won't listen to the messages sent
     *
     * @param MessageListenerInterface $element
     * @param string $messageId
     * @internal param string $id
     * @internal param MessageInterface $message
     *
     * @return mixed
     */
    public function unsubscribe(MessageListenerInterface $element, $messageId);
}
