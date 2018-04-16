<?php

namespace ProcessMaker\Nayra\Contracts\Bpmn;

/**
 * Assignment interface.
 *
 * @package ProcessMaker\Nayra\Contracts\Bpmn
 */
interface AssignmentInterface extends EntityInterface
{

    /**
     * 
     *
     * @return FormalExpressionInterface
     */
    public function getFrom();

    /**
     * 
     *
     * @return FormalExpressionInterface
     */
    public function getTo();
}
