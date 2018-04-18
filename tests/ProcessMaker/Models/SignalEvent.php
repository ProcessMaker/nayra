<?php

namespace ProcessMaker\Models;

use ProcessMaker\Nayra\Bpmn\FlowNodeTrait;
use ProcessMaker\Nayra\Contracts\Bpmn\MessageEventDefinitionInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\MessageListenerInterface;

/**
 * BPMN Signal event element
 *
 * @package ProcessMaker\Nayra\Contracts\Bpmn
 */
class SignalEvent implements MessageListenerInterface
{
    use FlowNodeTrait;

    /**
     * Method to be called when a message event arrives
     *
     * @param MessageEventDefinitionInterface $message
     * @return mixed
     */
    public function execute(MessageEventDefinitionInterface $message)
    {
        // TODO: Implement execute() method.
    }

    /**
     * Array map of custom event classes for the bpmn element.
     *
     * @return array
     */
    protected function getBpmnEventClasses()
    {
        // TODO: Implement getBpmnEventClasses() method.
    }

    /**
     * Concrete classes like Activities, Gateways, should implement a method
     * that build the connections to other nodes.
     *
     * @param FlowNodeInterface $target
     *
     * @return $this
     */
    protected function buildConnectionTo(FlowNodeInterface $target)
    {
        // TODO: Implement buildConnectionTo() method.
    }
}