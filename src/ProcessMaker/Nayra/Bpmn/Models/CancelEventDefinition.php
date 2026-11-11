<?php

namespace ProcessMaker\Nayra\Bpmn\Models;

use ProcessMaker\Nayra\Bpmn\EventDefinitionTrait;
use ProcessMaker\Nayra\Contracts\Bpmn\CancelEventDefinitionInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\EventDefinitionInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\FlowNodeInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\TokenInterface;
use ProcessMaker\Nayra\Contracts\Engine\ExecutionInstanceInterface;

/**
 * Cancel event definition.
 */
class CancelEventDefinition implements CancelEventDefinitionInterface
{
    use EventDefinitionTrait;

    /**
     * @param EventDefinitionInterface $event
     * @param FlowNodeInterface $target
     * @param ExecutionInstanceInterface|null $instance
     * @param TokenInterface|null $token
     *
     * @return bool
     */
    public function assertsRule(
        EventDefinitionInterface $event,
        FlowNodeInterface $target,
        ExecutionInstanceInterface $instance = null,
        TokenInterface $token = null
    ) {
        return $event instanceof CancelEventDefinitionInterface;
    }

    /**
     * @param EventDefinitionInterface $event
     * @param FlowNodeInterface $target
     * @param ExecutionInstanceInterface|null $instance
     * @param TokenInterface|null $token
     *
     * @return $this
     */
    public function execute(
        EventDefinitionInterface $event,
        FlowNodeInterface $target,
        ExecutionInstanceInterface $instance = null,
        TokenInterface $token = null
    ) {
        return $this;
    }
}
