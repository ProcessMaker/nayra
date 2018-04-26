<?php

namespace ProcessMaker\Nayra\Contracts\Bpmn;

use ProcessMaker\Nayra\Contracts\Engine\ExecutionInstanceInterface;

/**
 * Behavior that must implement objects that listen to message notifications
 *
 * @package ProcessMaker\Nayra\Contracts\Bpmn
 */
interface MessageListenerInterface
{
    /**
     * Method to be called when a message event arrives
     *
     * @param MessageEventDefinitionInterface $message
     * @return mixed
     */
    public function execute(MessageEventDefinitionInterface $message, ExecutionInstanceInterface $instance);
}
