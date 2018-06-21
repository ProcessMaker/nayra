<?php

namespace ProcessMaker\Nayra\Bpmn\Models;

use ProcessMaker\Nayra\Bpmn\ExclusiveGatewayTrait;
use ProcessMaker\Nayra\Contracts\Bpmn\ExclusiveGatewayInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\FlowInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\FlowNodeInterface;
use ProcessMaker\Nayra\Contracts\RepositoryInterface;

/**
 * Exclusive Gateway
 *
 * Has pass-through semantics for a set of incoming branches
 * (merging behavior). Further on, each activation leads to the activation of
 * exactly one out of the set of outgoing branches (branching behavior)
 *
 */
class ExclusiveGateway implements ExclusiveGatewayInterface
{
    use ExclusiveGatewayTrait;

    /**
     * For gateway connections.
     *
     * @param FlowNodeInterface $target
     * @param callable $condition
     * @param bool $isDefault
     * @param RepositoryInterface $factory
     *
     * @return $this
     *
     */
    public function createConditionedFlowTo(
        FlowNodeInterface $target,
        callable $condition,
        $isDefault,
        RepositoryInterface $factory
    ) {
        $this->createFlowTo($target, $factory, [
            FlowInterface::BPMN_PROPERTY_CONDITION_EXPRESSION => $condition,
            FlowInterface::BPMN_PROPERTY_IS_DEFAULT => $isDefault,
        ]);
        return $this;
    }

    /**
     * Array map of custom event classes for the bpmn element.
     *
     * @return array
     */
    protected function getBpmnEventClasses()
    {
        return [];
    }
}
