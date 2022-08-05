<?php

namespace ProcessMaker\Nayra\Contracts\Bpmn;

interface CorrelationPropertyRetrievalExpressionInterface
{
    /**
     * Get the specific Message the FormalExpression extracts the
     * CorrelationProperty from.
     *
     * @return MessageInterface
     */
    public function getMessage();

    /**
     * Get the FormalExpression that defines how to extract a
     * CorrelationProperty from the Message payload.
     *
     * @return FormalExpressionInterface
     */
    public function getMessagePath();
}
