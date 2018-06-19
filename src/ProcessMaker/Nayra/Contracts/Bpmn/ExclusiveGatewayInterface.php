<?php

namespace ProcessMaker\Nayra\Contracts\Bpmn;

/**
 * Exclusive Gateway Interface
 *
 */
interface ExclusiveGatewayInterface extends GatewayInterface
{

    /**
     * Returns the list of conditioned transitions of the gateway
     *
     * @return \ProcessMaker\Nayra\Bpmn\Collection
     */
    public function getConditionedTransitions();
}
