<?php

namespace ProcessMaker\Nayra\Contracts\Bpmn;

/**
 * OutputSet interface.
 *
 * @package ProcessMaker\Nayra\Contracts\Bpmn
 */
interface OutputSetInterface extends EntityInterface
{
    const BPMN_PROPERTY_DATA_OUTPUTS = 'dataOutputs';
    const BPMN_PROPERTY_DATA_OUTPUT_REFS = 'dataOutputRefs';

    /**
     * Get DataOutput elements that MAY collectively be outputted.
     *
     * @return DataOutputInterface[]
     */
    public function getDataOutputs();

    /**
     * Set DataOutput elements that MAY collectively be outputted.
     *
     * @param CollectionInterface $dataOutputs
     *
     * @return $this
     */
    public function setDataOutputs(CollectionInterface $dataOutputs);
}
