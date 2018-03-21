<?php

namespace ProcessMaker\Nayra\Bpmn;

use ProcessMaker\Nayra\Contracts\Bpmn\TransitionInterface;
use ProcessMaker\Nayra\Bpmn\TransitionTrait;

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
            TransitionInterface::EVENT_AFTER_TRANSIT,
            function() {
                $this->startCount--;
            }
        );
    }

    public function start()
    {
        $this->startCount++;
    }

    public function assertCondition()
    {
        return $this->startCount > 0;
    }

    public function hasAllRequiredTokens()
    {
        return true;
    }
}
