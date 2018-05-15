<?php

namespace ProcessMaker\Nayra\Contracts\Bpmn;

/**
 * CatchEvent interface.
 *
 * @package ProcessMaker\Nayra\Contracts\Bpmn
 */
interface CatchEventInterface extends EventInterface
{
    const BPMN_PROPERTY_PARALLEL_MULTIPLE = 'parallelMultiple';
    const BPMN_PROPERTY_EVENT_DEFINITIONS = 'eventDefinitions';

    /**
     * Get EventDefinitions that are triggers expected for a catch Event.
     *
     * @return EventDefinitionInterface[]
     */
    public function getEventDefinitions();

    /**
     * @return \ProcessMaker\Nayra\Engine\ExecutionInstance[]
     */
    public function getTargetInstances(EventDefinitionInterface $message, TokenInterface $token);
}
