<?php

namespace ProcessMaker\Nayra\Contracts\Bpmn;

/**
 * A Message Flow is used to show the flow of Messages between two Participants
 * that are prepared to send and receive them.
 *
 * @package ProcessMaker\Nayra\Contracts\Bpmn
 */
interface MessageFlowInterface extends EntityInterface
{

    const BPMN_PROPERTY_SOURCE = 'source';
    const BPMN_PROPERTY_TARGET = 'target';
    const BPMN_PROPERTY_SOURCE_REF = 'sourceRef';
    const BPMN_PROPERTY_TARGET_REF = 'targetRef';
    const BPMN_PROPERTY_COLLABORATION = 'collaboration';

    /**
     * @return InteractionNodeInterface
     */
    public function getSource();

    /**
     * @return InteractionNodeInterface
     */
    public function getTarget();


    /**
     * Set message of the message flow.
     *
     * @param \ProcessMaker\Nayra\Contracts\Bpmn\MessageInterface $message
     *
     * @return $this
     */
    public function setMessage(MessageInterface $message);

    /**
     * Get message of the message flow.
     *
     * @return MessageInterface
     */
    public function getMessage();

    /**
     * Set the source of the message flow.
     *
     * @param \ProcessMaker\Nayra\Contracts\Bpmn\ThrowEventInterface $source
     *
     * @return $this
     */
    public function setSource($source);

    /**
     * Set the target of the message flow.
     *
     * @param CatchEventInterface $target
     *
     * @return $this
     */
    public function setTarget($target);

    /**
     * Sets the collaboration
     *
     * @param CollaborationInterface $collaboration
     */
    public function setCollaboration(CollaborationInterface $collaboration);

    /**
     * @return CollaborationInterface
     */
    public function getCollaboration();
}
