<?php

namespace ProcessMaker\Nayra\Contracts\Bpmn;

/**
 * Cancel event definition used inside a Transaction.
 */
interface CancelEventDefinitionInterface extends EventDefinitionInterface
{
    const EVENT_THROW_EVENT_DEFINITION = 'ThrowCancelEvent';
}
