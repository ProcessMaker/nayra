<?php

namespace ProcessMaker\Nayra\Bpmn\Models;

use ProcessMaker\Nayra\Bpmn\EventDefinitionTrait;
use ProcessMaker\Nayra\Contracts\Bpmn\CatchEventInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\ConditionalEventDefinitionInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\EventDefinitionInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\FlowNodeInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\TokenInterface;
use ProcessMaker\Nayra\Contracts\Engine\EngineInterface;
use ProcessMaker\Nayra\Contracts\Engine\ExecutionInstanceInterface;

/**
 * ConditionalEventDefinition class
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
     * @param TokenInterface|null $token
     *
     * @return bool
     */
    public function assertsRule(EventDefinitionInterface $event, FlowNodeInterface $target, ExecutionInstanceInterface $instance = null, TokenInterface $token = null)
    {
        // Get context data
        if ($instance && $target instanceof CatchEventInterface) {
            $data = $instance->getDataStore()->getData();
            $tokenCatch = $target->getActiveState()->getTokens($instance)->item(0);
            $conditionals = $tokenCatch->getProperty('conditionals', []);
        } else {
            $process = $target->getOwnerProcess();
            $engine = $process->getEngine();
            $data = $engine->getDataStore()->getData();
            $conditionals = $process->getProperty('conditionals', []);
        }
        // Get previous value
        $key = $this->getId() ?: $this->getBpmnElement()->getNodePath();
        $previous = $conditionals[$key] ?? null;
        // Evaluate condition
        $condition = $this->getCondition();
        $current = $condition($data);
        // Update previous value
        $conditionals[$key] = $current;
        if ($instance && $target instanceof CatchEventInterface) {
            foreach ($target->getActiveState()->getTokens($instance) as $tokenCatch) {
                $tokenCatch->setProperty('conditionals', $conditionals);
            }
        } else {
            $process->setProperty('conditionals', $conditionals);
        }

        return !$previous && $current;
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
     * @param CatchEventInterface $element
     * @param TokenInterface|null $token
     *
     * @return void
     */
    public function catchEventActivated(EngineInterface $engine, CatchEventInterface $element, TokenInterface $token = null)
    {
    }
}
