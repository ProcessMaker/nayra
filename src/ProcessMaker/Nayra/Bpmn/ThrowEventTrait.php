<?php

namespace ProcessMaker\Nayra\Bpmn;

use ProcessMaker\Nayra\Contracts\Bpmn\EventDefinitionInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\ThrowEventInterface;

/**
 * Implementation of the behavior for a throw event.
 */
trait ThrowEventTrait
{
    use FlowNodeTrait;

    /**
     * Initialize catch event.
     */
    protected function initCatchEventTrait()
    {
        $this->setProperty(ThrowEventInterface::BPMN_PROPERTY_EVENT_DEFINITIONS, new Collection);
    }

    /**
     * Get the event definitions.
     *
     * @return EventDefinitionInterface[]
     */
    public function getEventDefinitions()
    {
        return $this->getProperty(ThrowEventInterface::BPMN_PROPERTY_EVENT_DEFINITIONS);
    }
}
