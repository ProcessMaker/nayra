<?php

namespace ProcessMaker\Nayra\Contracts\Bpmn;

/**
 * InputSet interface.
 */
interface InputSetInterface extends EntityInterface
{
    const BPMN_PROPERTY_DATA_INPUTS = 'dataInputs';

    const BPMN_PROPERTY_DATA_INPUT_REFS = 'dataInputRefs';

    /**
     * Get the DataInput elements that collectively make up this data requirement.
     *
     * @return DataInputInterface[]
     */
    public function getDataInputs();

    /**
     * Set the DataInput elements that collectively make up this data requirement.
     *
     * @param CollectionInterface $dataInputs
     *
     * @return $this
     */
    public function setDataInputs(CollectionInterface $dataInputs);
}
