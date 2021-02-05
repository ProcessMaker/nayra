<?php

namespace ProcessMaker\Nayra\Contracts\Bpmn;

/**
 * An ItemDefinition is used to define the payload of the Message.
 *
 * @package ProcessMaker\Nayra\Bpmn
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
     * @param string $dataOutput
     * @return self
     */
    public function setDataOutput(CollectionInterface $dataOutput);

    /**
     * @return DataInputInterface
     */
    public function getDataInput();

    /**
     * @param DataInputInterface[] $dataInput
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
