<?php

namespace ProcessMaker\Nayra\Bpmn;

use ProcessMaker\Nayra\Contracts\Bpmn\GatewayInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\TokenInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\TransitionInterface;
use ProcessMaker\Nayra\Contracts\Engine\ExecutionInstanceInterface;

/**
 * Verify the condition to transit and if not accomplished the tokens are consumed.
 *
 * @package ProcessMaker\Nayra\Bpmn
 */
class DefaultTransition implements TransitionInterface
{
    use TransitionTrait;

    /**
     * Returns true if the condition of the transition is met with the DataStore of the passed execution instance
     *
     * @param TokenInterface $token
     * @param ExecutionInstanceInterface $executionInstance
     * @return bool
     */
    public function assertCondition(TokenInterface $token, ExecutionInstanceInterface $executionInstance)
    {
        $executeDefaultTransition = true;
        foreach($this->owner->getConditionedTransitions() as $transition) {
            if ($transition->assertCondition($token, $executionInstance)) {
               $executeDefaultTransition = false;
               break;
            }
        }
        return $executeDefaultTransition;
    }
}
