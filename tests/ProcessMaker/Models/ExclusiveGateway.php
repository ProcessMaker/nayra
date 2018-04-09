<?php

namespace ProcessMaker\Models;

use ProcessMaker\Nayra\Bpmn\ExclusiveGatewayTrait;
use ProcessMaker\Nayra\Contracts\Bpmn\FlowNodeInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\GatewayInterface;
use ProcessMaker\Nayra\Contracts\Repositories\FlowRepositoryInterface;

class ExclusiveGateway implements GatewayInterface
{
    use ExclusiveGatewayTrait,
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
     *
     * @return $this
     */
    public function createConditionedFlowTo(
        FlowNodeInterface $target,
        callable $condition,
        $isDefault,
        FlowRepositoryInterface $flowRepository
    ) {
        $this->createFlowTo($target, $flowRepository, [
            'CONDITION' => $condition,
            'IS_DEFAULT' => $isDefault,
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
