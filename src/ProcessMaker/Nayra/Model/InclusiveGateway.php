<?php

namespace ProcessMaker\Nayra\Model;


use ProcessMaker\Nayra\Bpmn\InclusiveGatewayTrait;
use ProcessMaker\Nayra\Contracts\Bpmn\FlowInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\FlowNodeInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\InclusiveGatewayInterface;
use ProcessMaker\Nayra\Contracts\FactoryInterface;
use ProcessMaker\Nayra\Contracts\Repositories\FlowRepositoryInterface;

class InclusiveGateway implements InclusiveGatewayInterface
{

    use InclusiveGatewayTrait;

    /**
     * For gateway connections.
     *
     * @param FlowNodeInterface $target
     * @param callable $condition
     * @param $isDefault
     * @param FlowRepositoryInterface $flowRepository
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
     * Array map of custom event classes for the BPMN element.
     *
     * @return array
     */
    protected function getBpmnEventClasses()
    {
        return [];
    }
}
