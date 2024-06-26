<?php

namespace ProcessMaker\Nayra\Bpmn;

use ProcessMaker\Nayra\Contracts\Engine\ExecutionInstanceInterface;

/**
 * Process base implementation.
 */
trait PauseOnGatewayTransitionTrait
{
    use TransitionTrait;

    function shouldPauseGatewayTransition(ExecutionInstanceInterface $executionInstance)
    {
        $engine = $executionInstance->getEngine();
        $demoMode = $engine->isDemoMode();
        $gateway = $this->getOwner();
        return $demoMode && !$engine->getSelectedDemoFlow($gateway);
    }
}
