<?php

namespace ProcessMaker\Nayra\Bpmn;

use ProcessMaker\Nayra\Contracts\Bpmn\CatchEventInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\TokenInterface;
use ProcessMaker\Nayra\Contracts\Engine\EngineInterface;

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
     * @return \ProcessMaker\Nayra\Contracts\Bpmn\CollectionInterface
     */
    public function getEventDefinitions()
    {
        return $this->getProperty(CatchEventInterface::BPMN_PROPERTY_EVENT_DEFINITIONS);
    }

    /**
     * Register catch events.
     *
     * @param EngineInterface $engine
     *
     * @return $this
     */
    public function registerCatchEvents(EngineInterface $engine)
    {
        foreach ($this->getEventDefinitions() as $eventDefinition) {
            $eventDefinition->registerWithCatchEvent($engine, $this);
        }
        return $this;
    }

    /**
     * Register catch events.
     *
     * @param TokenInterface|null $token
     *
     * @return $this
     */
    private function activateCatchEvent(TokenInterface $token = null)
    {
        foreach ($this->getEventDefinitions() as $eventDefinition) {
            $eventDefinition->catchEventActivated($this->getOwnerProcess()->getEngine(), $this, $token);
        }
        return $this;
    }

    /**
     * Register the BPMN elements with the engine.
     *
     * @param EngineInterface $engine
     *
     * @return FlowElementInterface
     */
    public function registerWithEngine(EngineInterface $engine)
    {
        $this->registerCatchEvents($engine);
        return $this;
    }
}
