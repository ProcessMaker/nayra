<?php

namespace ProcessMaker\Models;

use ProcessMaker\Nayra\Bpmn\Collection;
use ProcessMaker\Nayra\Contracts\Bpmn\FlowInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\FlowNodeInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\GatewayInterface;
use ProcessMaker\Nayra\Contracts\Repositories\FlowRepositoryInterface;

/**
 * Trait to store flows as local variables.
 *
 * @package ProcessMaker\Models
 */
trait LocalFlowNodeTrait
{

    /**
     * @param FlowNodeInterface $target
     * @param FlowRepositoryInterface $flowRepository
     * @param array $properties
     * @return $this
     */
    public function createFlowTo(FlowNodeInterface $target, FlowRepositoryInterface $flowRepository, $properties=[])
    {
        $flow = $flowRepository->createFlowInstance();
        $flow->setSource($this);
        $flow->setTarget($target);
        $flow->setProperties($properties);
        $this->addProperty(FlowNodeInterface::BPMN_PROPERTY_OUTGOING, $flow);
        if (!empty($properties[FlowInterface::BPMN_PROPERTY_IS_DEFAULT])) {
            $this->setProperty(GatewayInterface::BPMN_PROPERTY_DEFAULT, $flow);
        }
        return $this;
    }

    /**
     * Get flows.
     *
     * @return array
     */
    public function getFlows()
    {
        return $this->getProperty(FlowNodeInterface::BPMN_PROPERTY_OUTGOING, new Collection);
    }
}
