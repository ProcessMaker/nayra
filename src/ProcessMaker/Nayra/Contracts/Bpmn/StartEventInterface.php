<?php

namespace ProcessMaker\Nayra\Contracts\Bpmn;

use ProcessMaker\Nayra\Contracts\Engine\ExecutionInstanceInterface;

/**
 * End event interface.
 *
 * @package ProcessMaker\Nayra\Contracts\Bpmn
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
}
