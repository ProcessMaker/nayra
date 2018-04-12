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

    //public function setMessageFlows(CollectionInterface $messageFlows);
}
