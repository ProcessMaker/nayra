<?php

namespace ProcessMaker\Nayra\Contracts\Bpmn;

/**
 * An ItemDefinition is used to define the payload of the Message.
 */
interface InputOutputSpecificationInterface extends EntityInterface
{
    const BPMN_PROPERTY_DATA_INPUT = 'dataInput';

    const BPMN_PROPERTY_DATA_OUTPUT = 'dataOutput';

    const BPMN_PROPERTY_DATA_INPUT_SET = 'inputSet';

    const BPMN_PROPERTY_DATA_OUTPUT_SET = 'outputSet';

    /**
     * @return DataOutputInterface[]
     */
    public function getDataOutput();

    /**
     * @param \ProcessMaker\Nayra\Contracts\Bpmn\CollectionInterface $dataOutput
     *
     * @return self
     */
    public function setDataOutput(CollectionInterface $dataOutput);

    /**
     * @return DataInputInterface
     */
    public function getDataInput();

    /**
     * @param \ProcessMaker\Nayra\Contracts\Bpmn\CollectionInterface $dataInput
     *
     * @return self
     */
    public function setDataInput(CollectionInterface $dataInput);

    /**
     * @return InputSetInterface
     */
    public function getInputSet();

    /**
     * @param InputSetInterface $inputSet
     * @return self
     */
    public function setInputSet(InputSetInterface $inputSet);

    /**
     * @return OutputSetInterface
     */
    public function getOutputSet();

    /**
     * @param OutputSetInterface $outputSet
     * @return self
     */
    public function setOutputSet(OutputSetInterface $outputSet);
}
