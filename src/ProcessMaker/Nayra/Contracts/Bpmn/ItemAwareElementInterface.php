<?php

namespace ProcessMaker\Nayra\Contracts\Bpmn;

/**
 * This element can store or convey items during process execution.
 *
 * @package ProcessMaker\Nayra\Contracts\Bpmn
 */
interface ItemAwareElementInterface extends FlowElementInterface
{
    /**
     * Get item state.
     *
     * @return mixed
     */
    public function getState();

    /**
     * Set item state.
     *
     * @param $state
     *
     * @return $this
     */
    public function setState($state);
}
