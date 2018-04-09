<?php

namespace ProcessMaker\Models;


use ProcessMaker\Nayra\Bpmn\ParallelGatewayTrait;
use ProcessMaker\Nayra\Contracts\Bpmn\FlowNodeInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\GatewayInterface;
use ProcessMaker\Nayra\Contracts\Repositories\FlowRepositoryInterface;
use ProcessMaker\Nayra\Exceptions\InvalidSequenceFlowException;

class ParallelGateway implements GatewayInterface
{

    use ParallelGatewayTrait,
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
        throw new InvalidSequenceFlowException('A parallel gateway can not have conditioned outgoing flows.');
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
