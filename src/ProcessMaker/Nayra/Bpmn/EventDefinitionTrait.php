<?php

namespace ProcessMaker\Nayra\Bpmn;

use ProcessMaker\Nayra\Contracts\Bpmn\CatchEventInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\EventDefinitionInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\TokenInterface;
use ProcessMaker\Nayra\Contracts\Engine\EngineInterface;
use ProcessMaker\Nayra\Contracts\Engine\ExecutionInstanceInterface;

/**
 * Base implementation for a exclusive gateway.
 */
trait EventDefinitionTrait
{
    use BaseTrait;

    /**
     * Initialize event definition ID if it was not defined in the bpmn model.
     */
    protected function initEventDefinitionTrait()
    {
        $this->setId(uniqid('event-definition-', true));
    }

    /**
     * Register event with a catch event
     *
     * @param EngineInterface $engine
     * @param CatchEventInterface $element
     */
    public function registerWithCatchEvent(EngineInterface $engine, CatchEventInterface $element)
    {
        $engine->getEventDefinitionBus()->registerCatchEvent($element, $this, function (EventDefinitionInterface $eventDefinition, ExecutionInstanceInterface $instance = null, TokenInterface $token = null) use ($element) {
            $element->execute($eventDefinition, $instance, $token);
        });
    }

    /**
     * Occurs when the catch event was activated
     *
     * @param EngineInterface $engine
     * @param CatchEventInterface $element
     * @param TokenInterface|null $token
     *
     * @return void
     */
    public function catchEventActivated(EngineInterface $engine, CatchEventInterface $element, TokenInterface $token = null)
    {
    }

    /**
     * Check if the event definition should be catch
     *
     * @param EventDefinitionInterface $sourceEvent
     *
     * @return bool
     */
    public function shouldCatchEventDefinition(EventDefinitionInterface $sourceEvent)
    {
        return true;
    }

    /**
     * Get data contained in the event payload
     *
     * @param TokenInterface|null $token
     * @param CatchEventInterface|null $target
     *
     * @return mixed
     */
    public function getPayloadData(TokenInterface $token = null, CatchEventInterface $target = null)
    {
        return $token ? $token->getInstance()->getDataStore()->getData() : [];
    }

    /**
     * Set do not trigger start events
     *
     * @param bool $value
     *
     * @return $this
     */
    public function setDoNotTriggerStartEvents($value)
    {
        $this->setProperty('doNotTriggerStartEvents', $value);

        return $this;
    }

    /**
     * Get do not trigger start events value
     *
     * @return bool
     */
    public function getDoNotTriggerStartEvents()
    {
        return $this->getProperty('doNotTriggerStartEvents', false);
    }

    /**
     * Returns the event of the event definition (message, signal, etc.)
     *
     * @return SignalInterface|MessageInterface|ErrorInterface|mixed
     */
    public function getPayload()
    {
        return null;
    }
}
