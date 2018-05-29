<?php

namespace ProcessMaker\Nayra\Bpmn;

use ProcessMaker\Nayra\Bpmn\TransitionTrait;
use ProcessMaker\Nayra\Contracts\Bpmn\FlowNodeInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\TokenInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\TransitionInterface;
use ProcessMaker\Nayra\Contracts\Engine\ExecutionInstanceInterface;

/**
 * Transition rule that always pass the token.
 *
 * @package ProcessMaker\Nayra\Bpmn
 */
class Transition implements TransitionInterface
{
    use TransitionTrait;

    /**
     * Initialize transition.
     *
     * @param \ProcessMaker\Nayra\Contracts\Bpmn\FlowNodeInterface $owner
     * @param bool $preserveToken
     */
    protected function initActivityTransition(FlowNodeInterface $owner, $preserveToken=true)
    {
        $this->setPreserveToken($preserveToken);
    }

    /**
     * Condition required to transit the element.
     *
     * @param \ProcessMaker\Nayra\Contracts\Bpmn\TokenInterface|null $token
     * @param \ProcessMaker\Nayra\Contracts\Engine\ExecutionInstanceInterface $executionInstance
     *
     * @return bool
     */
    public function assertCondition(TokenInterface $token = null, ExecutionInstanceInterface $executionInstance)
    {
        return true;
    }
}
