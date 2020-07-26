<?php

namespace ProcessMaker\Nayra\Bpmn\Models;

use ProcessMaker\Nayra\Bpmn\EventDefinitionTrait;
use ProcessMaker\Nayra\Contracts\Bpmn\CatchEventInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\ConditionalEventDefinitionInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\EventDefinitionInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\FlowElementInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\FlowNodeInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\TokenInterface;
use ProcessMaker\Nayra\Contracts\Engine\EngineInterface;
use ProcessMaker\Nayra\Contracts\Engine\ExecutionInstanceInterface;

/**
 * ConditionalEventDefinition class
 *
 */
class ConditionalEventDefinition implements ConditionalEventDefinitionInterface
{
    use EventDefinitionTrait;

    /**
     * Assert the event definition rule for trigger the event.
     *
     * @param EventDefinitionInterface $event
     * @param FlowNodeInterface $target
     * @param ExecutionInstanceInterface|null $instance
     *
     * @return boolean
     */
    public function assertsRule(EventDefinitionInterface $event, FlowNodeInterface $target, ExecutionInstanceInterface $instance = null)
    {
        if ($instance) {
            $data = $instance->getDataStore()->getData();
        } else {
            $engine = $target->getOwnerProcess()->getEngine();
            $data = $engine->getDataStore()->getData();
        }
        $condition = $this->getCondition();
        $res = $event instanceof ConditionalEventDefinition && $condition($data);
        return $res;
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

    /**
     * Implement the event definition behavior when an event is triggered.
     *
     * @param EventDefinitionInterface $event
     * @param FlowNodeInterface $target
     * @param ExecutionInstanceInterface|null $instance
     * @param TokenInterface|null $token
     *
     * @return $this
     */
    public function execute(EventDefinitionInterface $event, FlowNodeInterface $target, ExecutionInstanceInterface $instance = null, TokenInterface $token = null)
    {
        return $this;
    }

    /**
     * Occures when the catch event is activated
     *
     * @param EngineInterface $engine
     * @param FlowElementInterface $element
     * @param TokenInterface $token
     *
     * @return void
     */
    public function catchEventActivated(EngineInterface $engine, CatchEventInterface $element, TokenInterface $token = null)
    {
        $instance = $token ? $token->getInstance() : null;
        if ($instance) {
            $element->execute($this, $instance);
        }
    }
}
