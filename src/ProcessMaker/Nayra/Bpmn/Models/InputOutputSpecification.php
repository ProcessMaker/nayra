<?php

namespace ProcessMaker\Nayra\Bpmn\Models;

use ProcessMaker\Nayra\Bpmn\FlowElementTrait;
use ProcessMaker\Nayra\Contracts\Bpmn\CollectionInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\InputOutputSpecificationInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\InputSetInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\OutputSetInterface;

/**
 * InputOutputSpecification implementation.
 */
class InputOutputSpecification implements InputOutputSpecificationInterface
{
    use FlowElementTrait;

    /**
     * @return CollectionInterface
     */
    public function getDataOutput()
    {
        return $this->getProperty(self::BPMN_PROPERTY_DATA_OUTPUT);
    }

    /**
     * @param CollectionInterface $dataOutput
     *
     * @return static
     */
    public function setDataOutput(CollectionInterface $dataOutput)
    {
        return $this->setProperty(self::BPMN_PROPERTY_DATA_OUTPUT, $dataOutput);
    }

    /**
     * @return CollectionInterface
     */
    public function getDataInput()
    {
        return $this->getProperty(self::BPMN_PROPERTY_DATA_INPUT);
    }

    /**
     * @param CollectionInterface $dataInput
     *
     * @return static
     */
    public function setDataInput(CollectionInterface $dataInput)
    {
        return $this->setProperty(self::BPMN_PROPERTY_DATA_INPUT, $dataInput);
    }

    /**
     * @return InputSetInterface
     */
    public function getInputSet()
    {
        return $this->getProperty(self::BPMN_PROPERTY_DATA_INPUT_SET);
    }

    /**
     * @param InputSetInterface $inputSet
     *
     * @return static
     */
    public function setInputSet(InputSetInterface $inputSet)
    {
        return $this->setProperty(self::BPMN_PROPERTY_DATA_INPUT_SET, $inputSet);
    }

    /**
     * @return OutputSetInterface
     */
    public function getOutputSet()
    {
        return $this->getProperty(self::BPMN_PROPERTY_DATA_OUTPUT_SET);
    }

    /**
     * @param OutputSetInterface $outputSet
     *
     * @return static
     */
    public function setOutputSet(OutputSetInterface $outputSet)
    {
        return $this->setProperty(self::BPMN_PROPERTY_DATA_OUTPUT_SET, $outputSet);
    }
}
