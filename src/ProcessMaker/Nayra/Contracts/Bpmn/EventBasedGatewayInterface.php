<?php

namespace ProcessMaker\Nayra\Contracts\Bpmn;

/**
 * Event Based Gateway Interface
 */
interface EventBasedGatewayInterface extends GatewayInterface
{
    const EVENT_CATCH_EVENT_TRIGGERED = 'EventBasedGatewayCatchEventTriggered';

    /**
     * Get the next Event Elements after the gateway
     *
     * @return CatchEventInterface
     */
    public function getNextEventElements();
}
