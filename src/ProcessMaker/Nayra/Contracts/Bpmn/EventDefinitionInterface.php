<?php

namespace ProcessMaker\Nayra\Contracts\Bpmn;

use ProcessMaker\Nayra\Contracts\Bpmn\FlowNodeInterface;
use ProcessMaker\Nayra\Contracts\Engine\ExecutionInstanceInterface;

/**
 * EventDefinition interface.
 *
 * @package ProcessMaker\Nayra\Contracts\Bpmn
 */
interface EventDefinitionInterface extends EntityInterface
{
    const EVENT_THROW_EVENT_DEFINITION = 'EventDefinitionThrow';

    /**
     * Returns the element's id
     *
     * @return mixed
     */
    public function getId();

    /**
     * Sets the element id
     *
     * @param $value
     */
    public function setId($value);

    /**
     * Assert the event definition rule for trigger the event.
     *
     * @param EventDefinitionInterface $event
     * @param FlowNodeInterface $target
     * @param ExecutionInstanceInterface $instance
     *
     * @return boolean
     */
    public function assertsRule(EventDefinitionInterface $event, FlowNodeInterface $target, ExecutionInstanceInterface $instance = null);
}
