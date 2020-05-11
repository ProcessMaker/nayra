<?php

namespace ProcessMaker\Nayra\Bpmn\Models;

use ProcessMaker\Nayra\Bpmn\EventDefinitionTrait;
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
        $engine = $target->getOwnerProcess()->getEngine();
        $data = $engine->getDataStore()->getData();
        $condition = $this->getCondition();
        // TODO: code analyzer giving an error here - should be callable
        $res = $event instanceof ConditionalEventDefinition && $condition($data);
        error_log($res ? 'TRUE' : 'FALSE');
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
     * Occures when the catch event was activated
     *
     * @param EngineInterface $engine
     * @param FlowElementInterface $element
     * @param TokenInterface $token
     *
     * @return void
     */
    public function catchEventActivated(EngineInterface $engine, FlowElementInterface $element, TokenInterface $token = null)
    {
        $element->execute();
        error_log('entro');
        $this->execute($this, $element, $token ? $token->getInstance() : null, $token);
    }
}
