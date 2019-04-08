<?php

namespace ProcessMaker\Nayra\Bpmn\Models;

use ProcessMaker\Nayra\Bpmn\InclusiveGatewayTrait;
use ProcessMaker\Nayra\Contracts\Bpmn\FlowInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\FlowNodeInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\InclusiveGatewayInterface;
use ProcessMaker\Nayra\Contracts\RepositoryInterface;

/**
 * Inclusive Gateway
 *
 * Synchronizes a certain subset of branches out of the set
 * of concurrent incoming branches (merging behavior). Further on, each firing
 * leads to the creation of threads on a certain subset out of the set of
 * outgoing branches (branching behavior).
 *
 */
class InclusiveGateway implements InclusiveGatewayInterface
{
    use InclusiveGatewayTrait;

    /**
     * For gateway connections.
     *
     * @param FlowNodeInterface $target
     * @param callable $condition
     * @param boolean $isDefault
     * @param RepositoryInterface $factory
     *
     * @return $this
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
     * Array map of custom event classes for the BPMN element.
     *
     * @return array
     */
    protected function getBpmnEventClasses()
    {
        return [];
    }
}
