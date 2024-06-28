<?php

namespace ProcessMaker\Nayra\Bpmn;

use ProcessMaker\Nayra\Contracts\Bpmn\TokenInterface;
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

    /**
     * If the condition is not met.
     *
     * @param \ProcessMaker\Nayra\Contracts\Engine\ExecutionInstanceInterface $executionInstance
     *
     * @return bool
     */
    protected function conditionIsFalse()
    {
        $executionInstance = func_get_arg(0);
        $this->collect($executionInstance);

        return true;
    }

    /**
     * Consume the input tokens.
     *
     * @param \ProcessMaker\Nayra\Contracts\Engine\ExecutionInstanceInterface $executionInstance
     */
    private function collect(ExecutionInstanceInterface $executionInstance)
    {
        return $this->incoming()->sum(function (Connection $flow) use ($executionInstance) {
            return $flow->origin()->getTokens($executionInstance)->sum(function (TokenInterface $token) {
                return $token->getOwner()->consumeToken($token) ? 1 : 0;
            });
        });
    }
}
