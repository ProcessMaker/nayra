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

    /**
     * @return InteractionNodeInterface
     */
    public function getSource();

    /**
     * @return InteractionNodeInterface
     */
    public function getTarget();


    public function setMessage(MessageInterface $message);

    /**
     * @return MessageInterface
     */
    public function getMessage();

    /**
     * @return FlowNodeInterface $source
     */
    public function setSource(ThrowEventInterface $source);

    /**
     * @param FlowNodeInterface $target
     */
    public function setTarget(CatchEventInterface $target);

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
