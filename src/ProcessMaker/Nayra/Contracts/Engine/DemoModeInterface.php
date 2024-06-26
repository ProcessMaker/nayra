<?php

namespace ProcessMaker\Nayra\Contracts\Engine;

use ProcessMaker\Nayra\Contracts\Bpmn\FlowInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\GatewayInterface;

/**
 * Demo Mode interface.
 */
interface DemoModeInterface
{
    /**
     * Returns true if the engine is in demo mode.
     *
     * @return bool
     */
    public function isDemoMode();

    /**
     * Set if the engine is in demo mode.
     *
     * @param bool $value
     */
    public function setDemoMode(bool $value);

    /**
     * Retrieves the selected flow by the user in demo mode.
     *
     * @param GatewayInterface $gateway
     *
     * @return SequenceFlowInterface|null
     */
    public function getSelectedDemoFlow(GatewayInterface $gateway);

    /**
     * Set the selected flow by the user in demo mode.
     *
     * @param GatewayInterface $gateway
     * @param bool $value
     */
    public function setSelectedDemoFlow(
        GatewayInterface $gateway,
        FlowInterface $selectedFlow = null
    );
}
