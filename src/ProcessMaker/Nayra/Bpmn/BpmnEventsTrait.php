<?php

namespace ProcessMaker\Nayra\Bpmn;

/**
 * Trait to implements bpmn events handling.
 *
 * @package ProcessMaker\Nayra\Bpmn
 */
trait BpmnEventsTrait
{

    /**
     * Array map of custom event classes for the bpmn element.
     *
     * @return array
     */
    abstract protected function getBpmnEventClasses();

    /**
     * Fire a event for the current bpmn element.
     *
     * @param $event
     * @param array ...$arguments
     */
    protected function fireEvent($event, ...$arguments)
    {
        $bpmnEvents = $this->getBpmnEventClasses();
        if (isset($bpmnEvents[$event])) {
            $payload = new $bpmnEvents[$event]($this, $arguments);
        } else {
            $payload = ["object" => $this, "arguments" => $arguments];
        }
        $this->getOwnerProcess()->getDispatcher()->dispatch($event, $payload);
    }
}
