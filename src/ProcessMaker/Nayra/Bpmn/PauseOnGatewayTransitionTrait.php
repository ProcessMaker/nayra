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
        error_log(
            $gateway->getId()
            . '=' . json_encode($demoMode && !$engine->getSelectedDemoFlow($gateway))
        );
        return $demoMode && !$engine->getSelectedDemoFlow($gateway);
    }
}
