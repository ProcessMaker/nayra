<?php

namespace ProcessMaker\Nayra\Bpmn;

use ProcessMaker\Nayra\Contracts\Bpmn\ConditionalEventDefinitionInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\EventDefinitionInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\FlowNodeInterface;
use ProcessMaker\Nayra\Contracts\Engine\ExecutionInstanceInterface;

class ConditionalEventDefinition implements ConditionalEventDefinitionInterface
{

    use BaseTrait;

    /**
     * Assert the event definition rule for trigger the event.
     *
     * @param EventDefinitionInterface $event
     * @param FlowNodeInterface $target
     * @param ExecutionInstanceInterface $instance
     *
     * @return boolean
     */
    public function assertsRule(EventDefinitionInterface $event, FlowNodeInterface $target, ExecutionInstanceInterface $instance = null)
    {
        $engine = $target->getOwnerProcess()->getEngine();
        $data = $engine->getDataStore()->getData();
        $condition = $this->getCondition();
        return $event instanceof ConditionalEventDefinition && $condition($data);
    }

    /**
     * Get the condition of the event definition.
     *
     * @return \ProcessMaker\Nayra\Contracts\Bpmn\FormalExpressionInterface
     */
    public function getCondition()
    {
        return $this->getProperty(ConditionalEventDefinitionInterface::BPMN_PROPERTY_CONDITION);
    }
}
