<?php

namespace ProcessMaker\Nayra\Contracts\Bpmn;

/**
 * Inclusive Gateway Interface
 *
 */
interface InclusiveGatewayInterface extends GatewayInterface
{

    /**
     * Returns the list of conditioned transitions of the gateway
     *
     * @return \ProcessMaker\Nayra\Bpmn\Collection
     */
    public function getConditionedTransitions();
}
