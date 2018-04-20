<?php

namespace ProcessMaker\Nayra\Contracts\Bpmn;

/**
 * The Call Activity acts as a ‘wrapper’ for the invocation of a global
 * Process or Global Task within the execution.
 *
 * @package ProcessMaker\Nayra\Contracts\Bpmn
 */
interface CallActivityInterface extends ActivityInterface
{

    /**
     * Get the element to be called.
     *
     * @return ProcessInterface
     */
    public function getCalledElement();
}
