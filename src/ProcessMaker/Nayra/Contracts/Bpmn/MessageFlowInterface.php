<?php

namespace ProcessMaker\Nayra\Contracts\Bpmn;

/**
 * A Message Flow is used to show the flow of Messages between two Participants
 * that are prepared to send and receive them.
 *
 * @package ProcessMaker\Nayra\Contracts\Bpmn
 */
interface MessageFlowInterface
{

    /**
     * @return InteractionNodeInterface
     */
    public function getSource();

    /**
     * @return InteractionNodeInterface
     */
    public function getTarget();

    /**
     * @return MessageInterface
     */
    public function getMessage();
}
