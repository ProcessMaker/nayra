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

    public function assertCondition(TokenInterface $token= null, ExecutionInstanceInterface $executionInstance)
    {
        return $this->startCount > 0;
    }

    public function hasAllRequiredTokens(ExecutionInstanceInterface $executionInstance)
    {
        // if the start event is a normal event, always return true, otherwise check for the presence of
        //the trigger count by counting the number of tokens

        if ($this->owner->getEventDefinitions()->count() === 0) {
            return true;
        }
        else {
            return $this->incoming()->count() > 0 && $this->incoming()->find(function ($flow) use ($executionInstance) {
                    return $flow->origin()->getTokens($executionInstance)->count() === 0;
                })->count() === 0;
        }
    }
}
