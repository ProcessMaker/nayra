<?php

namespace ProcessMaker\Models;

use ProcessMaker\Nayra\Bpmn\InclusiveGatewayTrait;
use ProcessMaker\Nayra\Contracts\Bpmn\FlowInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\FlowNodeInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\GatewayInterface;
use ProcessMaker\Nayra\Contracts\Repositories\FlowRepositoryInterface;

class InclusiveGateway implements GatewayInterface
{

    use InclusiveGatewayTrait,
        LocalFlowNodeTrait,
        LocalProcessTrait,
        LocalPropertiesTrait;

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
        FlowRepositoryInterface $flowRepository
    ) {
        $this->createFlowTo($target, $flowRepository, [
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
