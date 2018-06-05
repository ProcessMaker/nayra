<?php

namespace ProcessMaker\Nayra\Bpmn\Model;

use ProcessMaker\Nayra\Bpmn\ParallelGatewayTrait;
use ProcessMaker\Nayra\Contracts\Bpmn\FlowNodeInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\ParallelGatewayInterface;
use ProcessMaker\Nayra\Contracts\FactoryInterface;
use ProcessMaker\Nayra\Contracts\Repositories\FlowRepositoryInterface;
use ProcessMaker\Nayra\Exceptions\InvalidSequenceFlowException;

class ParallelGateway implements ParallelGatewayInterface
{

    use ParallelGatewayTrait;

    /**
     * For gateway connections.
     *
     * @param FlowNodeInterface $target
     * @param callable $condition
     * @param $isDefault
     * @param FlowRepositoryInterface $factory
     * @return $this
     */
    public function createConditionedFlowTo(
        FlowNodeInterface $target,
        callable $condition,
        $isDefault,
        FactoryInterface $factory
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
