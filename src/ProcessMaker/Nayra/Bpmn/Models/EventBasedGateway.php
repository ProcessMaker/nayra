<?php

namespace ProcessMaker\Nayra\Bpmn\Models;

use ProcessMaker\Nayra\Contracts\Bpmn\FlowNodeInterface;
use ProcessMaker\Nayra\Contracts\RepositoryInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\EventBasedGatewayInterface;
use ProcessMaker\Nayra\Bpmn\EventBasedGatewayTrait;
use ProcessMaker\Nayra\Exceptions\InvalidSequenceFlowException;

/**
 * Event Based Gateway
 *
 * The Event-Based Gateway represents a branching point in the Process
 * where the alternative paths that follow the Gateway are based on
 * Events that occur, rather than the evaluation of Expressions using
 * Process data
 *
 */
class EventBasedGateway implements EventBasedGatewayInterface
{
    use EventBasedGatewayTrait;

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
