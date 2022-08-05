<?php

namespace ProcessMaker\Nayra\Contracts\Bpmn;

/**
 * MessageEventDefinition interface.
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
}
