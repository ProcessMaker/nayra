<?php

namespace ProcessMaker\Nayra\Bpmn;

use ProcessMaker\Nayra\Contracts\Engine\ExecutionInstanceInterface;

/**
 * Process base implementation.
 */
trait ForceGatewayTransitionTrait
{
    use TransitionTrait;

    /**
     * Check if the transition should be triggered in debug mode.
     *
     * @param ExecutionInstanceInterface $executionInstance
     *
     * @return bool
     */
    function shouldDebugTriggerThisTransition(ExecutionInstanceInterface $executionInstance)
    {
        $engine = $executionInstance->getEngine();
        $demoMode = $engine->isDemoMode();
        $gateway = $this->getOwner();
        $connection = $this->outgoing()->item(0);
        $targetEntrypoint = $connection->target()->getOwner();
        return $demoMode && $engine->getSelectedDemoFlow($gateway)->getTarget() === $targetEntrypoint;
    }

    /**
     * Check if the transition should be skipped in debug mode.
     *
     * @param ExecutionInstanceInterface $executionInstance
     *
     * @return bool
     */
    function shouldDebugSkipThisTransition(ExecutionInstanceInterface $executionInstance)
    {
        $engine = $executionInstance->getEngine();
        $demoMode = $engine->isDemoMode();
        $gateway = $this->getOwner();
        $connection = $this->outgoing()->item(0);
        $targetEntrypoint = $connection->target()->getOwner();
        return $demoMode && $engine->getSelectedDemoFlow($gateway)->getTarget() !== $targetEntrypoint;
    }
}
