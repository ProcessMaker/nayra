<?php

namespace ProcessMaker\Nayra\Contracts\Bpmn;

use ProcessMaker\Nayra\Contracts\Engine\ExecutionInstanceInterface;

/**
 * End event interface.
 */
interface StartEventInterface extends CatchEventInterface
{
    /**
     * Start event.
     *
     * @param \ProcessMaker\Nayra\Contracts\Engine\ExecutionInstanceInterface $instance
     *
     * @return $this;
     */
    public function start(ExecutionInstanceInterface $instance);

    /**
     * Method to be called when a message event arrives
     *
     * @param EventDefinitionInterface $event
     * @param ExecutionInstanceInterface|null $instance
     * @param TokenInterface|null $token
     *
     * @return $this
     */
    public function execute(EventDefinitionInterface $event, ExecutionInstanceInterface $instance = null, TokenInterface $token = null);
}
