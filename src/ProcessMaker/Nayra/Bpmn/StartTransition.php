<?php

namespace ProcessMaker\Nayra\Bpmn;

use ProcessMaker\Nayra\Bpmn\TransitionTrait;
use ProcessMaker\Nayra\Contracts\Bpmn\TokenInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\TransitionInterface;
use ProcessMaker\Nayra\Contracts\Engine\ExecutionInstanceInterface;

/**
 * Transition rule for a start event.
 *
 * @package ProcessMaker\Nayra\Bpmn
 */
class StartTransition implements TransitionInterface
{

    use TransitionTrait;
    private $startCount = 0;

    protected function initStartTransition()
    {
        $this->attachEvent(
            TransitionInterface::EVENT_AFTER_CONSUME,
            function() {
                $this->startCount--;
            }
        );
    }

    public function start()
    {
        $this->startCount++;
    }

    public function assertCondition(TokenInterface $token = null, ExecutionInstanceInterface $executionInstance)
    {
        return $this->startCount > 0;
    }

    public function hasAllRequiredTokens()
    {
        return true;
    }
}
