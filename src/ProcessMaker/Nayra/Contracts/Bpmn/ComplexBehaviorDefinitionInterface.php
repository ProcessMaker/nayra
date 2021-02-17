<?php

namespace ProcessMaker\Nayra\Contracts\Bpmn;

/**
 * Complex Behavior Definition This element controls when and which Events are
 * thrown in case behavior of the Multi-Instance Activity is set to complex.
 *
 * @package ProcessMaker\Nayra\Contracts\Bpmn
 */
interface ComplexBehaviorDefinitionInterface extends EntityInterface
{
    const BPMN_PROPERTY_CONDITION = 'condition';
    const BPMN_PROPERTY_EVENT = 'event';

    /**
     * @return FormalExpressionInterface
     */
    public function getCondition();

    /**
     * @param FormalExpressionInterface $condition
     *
     * @return self
     */
    public function setCondition(FormalExpressionInterface $condition);

    /**
     * @return mixed
     */
    public function getEvent();

    /**
     * @param mixed $event
     *
     * @return self
     */
    public function setEvent($event);
}
