<?php

namespace ProcessMaker\Nayra\Contracts\Bpmn;

use ProcessMaker\Nayra\Contracts\Bpmn\ErrorInterface;

/**
 * ErrorEventDefinition interface.
 */
interface ErrorEventDefinitionInterface extends EventDefinitionInterface
{
    const BPMN_PROPERTY_ERROR_REF = 'errorRef';

    const BPMN_PROPERTY_ERROR = 'error';

    const EVENT_THROW_EVENT_DEFINITION = 'ThrowErrorEvent';

    const EVENT_CATCH_EVENT_DEFINITION = 'CatchErrorEvent';

    /**
     * Get the error of the event definition.
     *
     * @return ErrorInterface
     */
    public function getError();

    /**
     * Returns the event definition payload (message, signal, etc.)
     *
     * @return ErrorInterface
     */
    public function getPayload();

    /**
     * Set the error of the event definition.
     *
     * @param ErrorInterface $error
     *
     * @return $this
     */
    public function setError(ErrorInterface $error);

    /**
     * Check if the $eventDefinition should be catch
     *
     * @param EventDefinitionInterface $eventDefinition
     *
     * @return bool
     */
    public function shouldCatchEventDefinition(EventDefinitionInterface $eventDefinition);
}
