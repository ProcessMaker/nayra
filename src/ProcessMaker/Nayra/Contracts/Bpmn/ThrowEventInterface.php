<?php

namespace ProcessMaker\Nayra\Contracts\Bpmn;

/**
 * ThrowEvent interface.
 *
 * @package ProcessMaker\Nayra\Contracts\Bpmn
 */
interface ThrowEventInterface extends EventInterface
{

    /**
     * Events defined for the the throw event interface
     */
    const EVENT_THROW_TOKEN_ARRIVES = 'ThrowEventTokenArrives';
    const EVENT_THROW_EXCEPTION = 'ThrowEventException';
    const EVENT_THROW_TOKEN_PASSED = 'ThrowEventTokenPassed';
    const EVENT_THROW_TOKEN_CONSUMED = 'ThrowEventTokenConsumed';

    /**
     * Get Data Inputs for the throw Event.
     *
     * @return DataInputInterface[]
     */
    public function getDataInputs();

    /**
     * Get Data Associations of the throw Event.
     *
     * @return DataInputAssociationInterface[]
     */
    public function getDataInputAssociations();

    /**
     * Get InputSet for the throw Event.
     *
     * @return InputSetInterface
     */
    public function getInputSet();
}
