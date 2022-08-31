<?php

namespace ProcessMaker\Nayra\Contracts\Bpmn;

/**
 * ConditionalEventDefinition interface.
 */
interface TerminateEventDefinitionInterface extends EventDefinitionInterface
{
    const EVENT_THROW_EVENT_DEFINITION = 'ThrowTerminateEvent';

    const EVENT_CATCH_EVENT_DEFINITION = 'CatchTerminateEvent';
}
