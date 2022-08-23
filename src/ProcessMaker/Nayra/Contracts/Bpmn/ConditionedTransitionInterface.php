<?php

namespace ProcessMaker\Nayra\Contracts\Bpmn;

/**
 * Interface for a conditioned transition.
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
