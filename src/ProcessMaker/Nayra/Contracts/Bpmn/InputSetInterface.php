<?php

namespace ProcessMaker\Nayra\Contracts\Bpmn;

/**
 * InputSet interface.
 *
 * @package ProcessMaker\Nayra\Contracts\Bpmn
 */
interface InputSetInterface extends EntityInterface
{

    /**
     * Get the DataInput elements that collectively make up this data requirement.
     *
     * @return DataInputInterface[]
     */
    public function getDataInputs();

    /**
     * Get DataInput elements that are a part of the InputSet that can be in
     * the state of "unavailable" when the Activity starts executing.
     *
     * @return DataInputInterface[]
     */
    public function getOptionalInputs();

    /**
     * Get DataInput elements that are a part of the InputSet that can be
     * evaluated while the Activity is executing.
     *
     * @return DataInputInterface[]
     */
    public function getWhileExecutingInputs();

    /**
     * 
     *
     * @return OutputSetInterface[]
     */
    public function getOutputSets();

}
