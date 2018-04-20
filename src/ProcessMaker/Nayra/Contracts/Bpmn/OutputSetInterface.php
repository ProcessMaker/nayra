<?php

namespace ProcessMaker\Nayra\Contracts\Bpmn;

/**
 * OutputSet interface.
 *
 * @package ProcessMaker\Nayra\Contracts\Bpmn
 */
interface OutputSetInterface extends EntityInterface
{

    /**
     * Get DataOutput elements that MAY collectively be outputted.
     *
     * @return DataOutputInterface[]
     */
    public function getDataOutputs();

    /**
     * Get DataOutput elements that are a part of the OutputSet that
     * do not have to be produced when the Activity completes executing.
     *
     * @return DataOutputInterface[]
     */
    public function getOptionalOutputs();

    /**
     * Get DataOutput elements that are a part of the OutputSet that can
     * be produced while the Activity is executing.
     *
     * @return DataOutputInterface[]
     */
    public function getWhileExecutingOutputs();

    /**
     * Get Specifies an Input/Output rule that defines which InputSet has
     * to become valid to expect the creation of this OutputSet.
     *
     * @return InputSetInterface[]
     */
    public function getInputSets();

}
