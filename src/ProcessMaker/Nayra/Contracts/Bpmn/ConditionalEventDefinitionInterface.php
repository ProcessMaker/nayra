<?php

namespace ProcessMaker\Nayra\Contracts\Bpmn;

/**
 * ConditionalEventDefinition interface.
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

    /**
     * Check if the $eventDefinition should be catch
     *
     * @param EventDefinitionInterface $eventDefinition
     *
     * @return bool
     */
    public function shouldCatchEventDefinition(EventDefinitionInterface $eventDefinition);
}
