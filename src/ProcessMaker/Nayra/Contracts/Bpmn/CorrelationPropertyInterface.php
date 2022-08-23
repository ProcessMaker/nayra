<?php

namespace ProcessMaker\Nayra\Contracts\Bpmn;

interface CorrelationPropertyInterface extends EntityInterface
{
    /**
     * Get the type of the CorrelationProperty.
     *
     * @return string
     */
    public function getType();

    /**
     * Get the CorrelationPropertyRetrievalExpressions for this CorrelationProperty,
     * representing the associations of FormalExpressions (extraction paths) to
     * specific Messages occurring in this Conversation.
     *
     * @return CorrelationPropertyRetrievalExpressionInterface[]
     */
    public function getCorrelationPropertyRetrievalExpressions();
}
