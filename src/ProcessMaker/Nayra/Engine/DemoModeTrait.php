<?php

namespace ProcessMaker\Nayra\Engine;

use ProcessMaker\Nayra\Contracts\Bpmn\FlowInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\GatewayInterface;

/**
 * Implements the engine behavior for debugging.
 */
trait DemoModeTrait
{
    /**
     * Is debug mode enabled
     *
     * @var bool
     */
    private $demoMode = false;

    /**
     * Flow selected by the user in demo mode.
     * @var FlowInterface[]
     */
    private $selectedFlows = [];

    /**
     * Returns true if the engine is in demo mode.
     *
     * @return bool
     */
    public function isDemoMode()
    {
        return $this->demoMode;
    }

    /**
     * Set if the engine is in demo mode.
     *
     * @param bool $value
     */
    public function setDemoMode(bool $value)
    {
        $this->demoMode = $value;
    }

    /**
     * Retrieves the selected flow by the user in demo mode.
     *
     * @param GatewayInterface $gateway
     *
     * @return FlowInterface|null
     */
    public function getSelectedDemoFlow(GatewayInterface $gateway)
    {
        return $this->selectedFlow[$gateway->getId()] ?? null;
    }

    /**
     * Set the selected flow by the user in demo mode.
     *
     * @param GatewayInterface $gateway
     * @param bool $value
     */
    public function setSelectedDemoFlow(
        GatewayInterface $gateway,
        FlowInterface $selectedFlow = null
    ) {
        $this->selectedFlow[$gateway->getId()] = $selectedFlow;
    }
}
