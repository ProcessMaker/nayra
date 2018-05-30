<?php

namespace ProcessMaker\Models;

use ProcessMaker\Nayra\Bpmn\ExclusiveGatewayTrait;
use ProcessMaker\Nayra\Contracts\Bpmn\ExclusiveGatewayInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\FlowInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\FlowNodeInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\GatewayInterface;
use ProcessMaker\Nayra\Contracts\FactoryInterface;
use ProcessMaker\Nayra\Contracts\Repositories\FlowRepositoryInterface;

class ExclusiveGateway implements ExclusiveGatewayInterface
{
    use ExclusiveGatewayTrait;

    /**
     * For gateway connections.
     *
     * @param FlowNodeInterface $target
     * @param callable $condition
     * @param $isDefault
     * @param FlowRepositoryInterface $factory
     *
     * @return $this
     */
    public function createConditionedFlowTo(
        FlowNodeInterface $target,
        callable $condition,
        $isDefault,
        FactoryInterface $factory
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
