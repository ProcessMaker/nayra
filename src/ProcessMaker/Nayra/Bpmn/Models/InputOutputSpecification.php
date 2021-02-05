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

    public function getDataOutput()
    {
        return $this->getProperty(self::BPMN_PROPERTY_DATA_OUTPUT);
    }

    public function setDataOutput(CollectionInterface $dataOutput)
    {
        return $this->setProperty(self::BPMN_PROPERTY_DATA_OUTPUT, $dataOutput);
    }

    public function getDataInput()
    {
        return $this->getProperty(self::BPMN_PROPERTY_DATA_INPUT);
    }

    public function setDataInput(CollectionInterface $dataInput)
    {
        return $this->setProperty(self::BPMN_PROPERTY_DATA_INPUT, $dataInput);
    }

    public function getInputSet()
    {
        return $this->getProperty(self::BPMN_PROPERTY_DATA_INPUT_SET);
    }

    public function setInputSet(InputSetInterface $inputSet)
    {
        return $this->setProperty(self::BPMN_PROPERTY_DATA_INPUT_SET, $inputSet);
    }

    public function getOutputSet()
    {
        return $this->getProperty(self::BPMN_PROPERTY_DATA_OUTPUT_SET);
    }

    public function setOutputSet(OutputSetInterface $outputSet)
    {
        return $this->setProperty(self::BPMN_PROPERTY_DATA_OUTPUT_SET, $outputSet);
    }
}
