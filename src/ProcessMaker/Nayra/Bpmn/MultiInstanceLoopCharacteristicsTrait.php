<?php

namespace ProcessMaker\Nayra\Bpmn;

use ProcessMaker\Nayra\Contracts\Bpmn\DataInputInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\DataOutputInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\FormalExpressionInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\MultiInstanceLoopCharacteristicsInterface;

/**
 * Base implementation for LoopCharacteristicsInterface
 *
 * @implements ProcessMaker\Nayra\Contracts\Bpmn\MultiInstanceLoopCharacteristicsInterface
 * @package ProcessMaker\Nayra\Bpmn
 */
trait MultiInstanceLoopCharacteristicsTrait
{
    use LoopCharacteristicsTrait;

    public function getIsSequential()
    {
        return $this->getProperty(MultiInstanceLoopCharacteristicsInterface::BPMN_PROPERTY_IS_SEQUENTIAL);
    }

    public function setIsSequential(bool $isSequential)
    {
        return $this->setProperty(MultiInstanceLoopCharacteristicsInterface::BPMN_PROPERTY_IS_SEQUENTIAL, $isSequential);
    }

    public function getBehavior()
    {
        return $this->getProperty(MultiInstanceLoopCharacteristicsInterface::BPMN_PROPERTY_BEHAVIOR);
    }

    public function setBehavior($behavior)
    {
        return $this->setProperty(MultiInstanceLoopCharacteristicsInterface::BPMN_PROPERTY_BEHAVIOR, $behavior);
    }

    public function getOneBehaviorEventRef()
    {
        return $this->getProperty(MultiInstanceLoopCharacteristicsInterface::BPMN_PROPERTY_ONE_BEHAVIOR_EVENT_REF);
    }

    public function setOneBehaviorEventRef($oneBehaviorEventRef)
    {
        return $this->setProperty(MultiInstanceLoopCharacteristicsInterface::BPMN_PROPERTY_ONE_BEHAVIOR_EVENT_REF, $oneBehaviorEventRef);
    }

    public function getNoneBehaviorEventRef()
    {
        return $this->getProperty(MultiInstanceLoopCharacteristicsInterface::BPMN_PROPERTY_NONE_BEHAVIOR_EVENT_REF);
    }

    public function setNoneBehaviorEventRef($noneBehaviorEventRef)
    {
        return $this->setProperty(MultiInstanceLoopCharacteristicsInterface::BPMN_PROPERTY_NONE_BEHAVIOR_EVENT_REF, $noneBehaviorEventRef);
    }

    public function getLoopCardinality()
    {
        return $this->getProperty(MultiInstanceLoopCharacteristicsInterface::BPMN_PROPERTY_LOOP_CARDINALITY);
    }

    public function setLoopCardinality(FormalExpressionInterface $loopCardinality)
    {
        return $this->setProperty(MultiInstanceLoopCharacteristicsInterface::BPMN_PROPERTY_LOOP_CARDINALITY, $loopCardinality);
    }

    public function getLoopDataInputRef()
    {
        return $this->getProperty(MultiInstanceLoopCharacteristicsInterface::BPMN_PROPERTY_LOOP_DATA_INPUT_REF);
    }

    public function setLoopDataInputRef($loopDataInputRef)
    {
        return $this->setProperty(MultiInstanceLoopCharacteristicsInterface::BPMN_PROPERTY_LOOP_DATA_INPUT_REF, $loopDataInputRef);
    }

    /**
     * @return DataInputInterface
     */
    public function getLoopDataInput()
    {
        return $this->getProperty(MultiInstanceLoopCharacteristicsInterface::BPMN_PROPERTY_LOOP_DATA_INPUT);
    }

    public function setLoopDataInput(DataInputInterface $loopDataInput)
    {
        return $this->setProperty(MultiInstanceLoopCharacteristicsInterface::BPMN_PROPERTY_LOOP_DATA_INPUT, $loopDataInput);
    }

    public function getLoopDataOutputRef()
    {
        return $this->getProperty(MultiInstanceLoopCharacteristicsInterface::BPMN_PROPERTY_LOOP_DATA_OUTPUT_REF);
    }

    public function setLoopDataOutputRef($loopDataOutputRef)
    {
        return $this->setProperty(MultiInstanceLoopCharacteristicsInterface::BPMN_PROPERTY_LOOP_DATA_OUTPUT_REF, $loopDataOutputRef);
    }

    /**
     * @return DataOutputInterface
     */
    public function getLoopDataOutput()
    {
        return $this->getProperty(MultiInstanceLoopCharacteristicsInterface::BPMN_PROPERTY_LOOP_DATA_OUTPUT);
    }

    public function setLoopDataOutput(DataOutputInterface $loopDataOutput)
    {
        return $this->setProperty(MultiInstanceLoopCharacteristicsInterface::BPMN_PROPERTY_LOOP_DATA_OUTPUT, $loopDataOutput);
    }

    public function getInputDataItem()
    {
        return $this->getProperty(MultiInstanceLoopCharacteristicsInterface::BPMN_PROPERTY_INPUT_DATA_ITEM);
    }

    public function setInputDataItem(DataInputInterface $inputDataItem)
    {
        return $this->setProperty(MultiInstanceLoopCharacteristicsInterface::BPMN_PROPERTY_INPUT_DATA_ITEM, $inputDataItem);
    }

    public function getOutputDataItem()
    {
        return $this->getProperty(MultiInstanceLoopCharacteristicsInterface::BPMN_PROPERTY_OUTPUT_DATA_ITEM);
    }

    public function setOutputDataItem(DataOutputInterface $outputDataItem)
    {
        return $this->setProperty(MultiInstanceLoopCharacteristicsInterface::BPMN_PROPERTY_OUTPUT_DATA_ITEM, $outputDataItem);
    }

    public function getComplexBehaviorDefinition()
    {
        return $this->getProperty(MultiInstanceLoopCharacteristicsInterface::BPMN_PROPERTY_COMPLEX_BEHAVIOR_DEFINITION);
    }

    public function setComplexBehaviorDefinition($complexBehaviorDefinition)
    {
        return $this->setProperty(MultiInstanceLoopCharacteristicsInterface::BPMN_PROPERTY_COMPLEX_BEHAVIOR_DEFINITION, $complexBehaviorDefinition);
    }

    public function getCompletionCondition()
    {
        return $this->getProperty(MultiInstanceLoopCharacteristicsInterface::BPMN_PROPERTY_COMPLETION_CONDITION);
    }

    public function setCompletionCondition(FormalExpressionInterface $completionCondition)
    {
        return $this->setProperty(MultiInstanceLoopCharacteristicsInterface::BPMN_PROPERTY_COMPLETION_CONDITION, $completionCondition);
    }
}
