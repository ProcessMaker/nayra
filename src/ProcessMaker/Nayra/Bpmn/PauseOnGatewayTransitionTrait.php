<?php

namespace ProcessMaker\Nayra\Bpmn;

use ProcessMaker\Nayra\Contracts\Engine\ExecutionInstanceInterface;

/**
 * Process base implementation.
 */
trait PauseOnGatewayTransitionTrait
{
    use TransitionTrait;

    /**
     * Check if the gateway should pause the transition.
     *
     * @param ExecutionInstanceInterface $executionInstance
     *
     * @return bool
     */
    function shouldPauseGatewayTransition(ExecutionInstanceInterface $executionInstance)
    {
        $engine = $executionInstance->getEngine();
        $demoMode = $engine->isDemoMode();
        $gateway = $this->getOwner();
        return $demoMode && !$engine->getSelectedDemoFlow($gateway);
    }

    /**
     * Check if the gateway input has at least one token in demo mode.
     *
     * @param ExecutionInstanceInterface $executionInstance
     * @return bool
     */
    function doesDemoHasAllRequiredTokens(ExecutionInstanceInterface $executionInstance)
    {
        $engine = $executionInstance->getEngine();
        $demoMode = $engine->isDemoMode();
        if (!$demoMode) {
            return false;
        }

        $withToken = $this->incoming()->find(function (Connection $flow) use ($executionInstance) {
            return $flow->originState()->getTokens($executionInstance)->count() > 0;
        });

        return $withToken->count() > 0;
    }
}
