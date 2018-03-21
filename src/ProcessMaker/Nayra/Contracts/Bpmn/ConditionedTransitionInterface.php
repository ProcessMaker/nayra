<?php

namespace ProcessMaker\Nayra\Contracts\Bpmn;

/**
 * Interface for a conditioned transition.
 *
 * @package ProcessMaker\Nayra\Contracts\Bpmn
 */
interface ConditionedTransitionInterface
{
    /**
     * Set the condition function.
     *
     * @param callable $condition
     *
     * @return $this
     */
    public function setCondition(callable $condition);

}