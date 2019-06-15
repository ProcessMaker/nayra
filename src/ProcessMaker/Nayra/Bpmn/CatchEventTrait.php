<?php

namespace ProcessMaker\Nayra\Bpmn;

use ProcessMaker\Nayra\Contracts\Bpmn\CatchEventInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\EventDefinitionInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\TokenInterface;

/**
 * Implementation of the behavior for a catch event.
 *
 * @package ProcessMaker\Nayra\Bpmn
 */
trait CatchEventTrait
{
    use FlowNodeTrait;

    /**
     * Initialize catch event.
     *
     */
    protected function initCatchEventTrait()
    {
        $this->setProperty(CatchEventInterface::BPMN_PROPERTY_EVENT_DEFINITIONS, new Collection);
        $this->setProperty(CatchEventInterface::BPMN_PROPERTY_PARALLEL_MULTIPLE, false);
    }

    /**
     * Get the event definitions.
     *
     * @return EventDefinitionInterface[]
     */
    public function getEventDefinitions()
    {
        return $this->getProperty(CatchEventInterface::BPMN_PROPERTY_EVENT_DEFINITIONS);
    }
}
