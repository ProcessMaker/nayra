<?php

namespace ProcessMaker\Nayra\Contracts\Bpmn;

/**
 * DataAssociation interface.
 *
 * @package ProcessMaker\Nayra\Contracts\Bpmn
 */
interface DataAssociationInterface extends EntityInterface
{

    /**
     * Get the source of the data association.
     *
     * @return ItemAwareElementInterface[]
     */
    public function getSources();

    /**
     * Get the target of the data association.
     *
     * @return ItemAwareElementInterface
     */
    public function getTarget();

    /**
     * Get an optional transformation Expression.
     *
     * @return FormalExpressionInterface
     */
    public function getTransformation();

    /**
     * 
     *
     * @return AssignmentInterface[]
     */
    public function getAssignmentInterfaces();
}
