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
