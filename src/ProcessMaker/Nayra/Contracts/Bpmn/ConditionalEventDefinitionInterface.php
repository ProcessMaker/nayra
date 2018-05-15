<?php

namespace ProcessMaker\Nayra\Contracts\Bpmn;

/**
 * ConditionalEventDefinition interface.
 *
 * @package ProcessMaker\Nayra\Contracts\Bpmn
 */
interface ConditionalEventDefinitionInterface extends EventDefinitionInterface
{
    const BPMN_PROPERTY_CONDITION = 'condition';

    /**
     * Get the condition to trigger the event.
     *
     * @return FormalExpressionInterface
     */
    public function getCondition();
}
