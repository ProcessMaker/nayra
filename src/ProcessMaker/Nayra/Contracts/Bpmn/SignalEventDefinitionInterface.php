<?php

namespace ProcessMaker\Nayra\Contracts\Bpmn;

use ProcessMaker\Nayra\Contracts\Engine\EngineInterface;

/**
 * MessageEventDefinition interface.
 *
 * @package ProcessMaker\Nayra\Contracts\Bpmn
 */
interface SignalEventDefinitionInterface extends EventDefinitionInterface
{
    const EVENT_THROW_EVENT_DEFINITION = 'ThrowSignalEvent';
    const EVENT_CATCH_EVENT_DEFINITION = 'CatchSignalEvent';

    const BPMN_PROPERTY_SIGNAL = 'signal';
    const BPMN_PROPERTY_SIGNAL_REF = 'signalRef';

    /**
     * Returns the event definition payload (message, signal, etc.)
     *
     * @return mixed
     */
    public function getPayload();

    /**
     * Sets the payload (message, signal, etc.)
     *
     * @param mixed $value
     *
     * @return $this
     */
    public function setPayload($value);

    /**
     * Check if the $eventDefinition should be catch
     *
     * @param EventDefinitionInterface $eventDefinition
     *
     * @return bool
     */
    public function shouldCatchEventDefinition(EventDefinitionInterface $eventDefinition);

    /**
     * Register in catch events.
     *
     * @param EngineInterface $engine
     * @param FlowElementInterface $element
     * @param TokenInterface|null $token
     */
    public function registerCatchEvents(EngineInterface $engine, FlowElementInterface $element, TokenInterface $token = null);
}
