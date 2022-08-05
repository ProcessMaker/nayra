<?php

namespace ProcessMaker\Nayra\Contracts\Bpmn;

/**
 * Assignment interface.
 */
interface AssignmentInterface extends EntityInterface
{
    /**
     * @return FormalExpressionInterface
     */
    public function getFrom();

    /**
     * @return FormalExpressionInterface
     */
    public function getTo();
}
