<?php

namespace ProcessMaker\Nayra\Bpmn;

/**
 * Flow connects to flow node elements.
 *
 * @package ProcessMaker\Nayra\Bpmn
 */
trait FlowTrait
{
    use BaseTrait;
    use BpmnEventsTrait;

    public function registerFlowEvents()
    {
        $this->getTransition()->attachEvent(
            TransitionInterface::EVENT_AFTER_CONSUME,
            function ($transition, $tokens) {
                $this->notifyEvent(FlowInterface::EVENT_FLOW_ACTIVATED);
            }
        );
    }
}
