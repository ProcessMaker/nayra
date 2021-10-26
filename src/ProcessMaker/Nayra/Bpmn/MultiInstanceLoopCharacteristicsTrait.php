<?php

namespace ProcessMaker\Nayra\Bpmn;

use ProcessMaker\Nayra\Contracts\Bpmn\ComplexBehaviorDefinitionInterface;
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

    /**
     * @return bool
     */
    public function getIsSequential()
    {
        return $this->getProperty(MultiInstanceLoopCharacteristicsInterface::BPMN_PROPERTY_IS_SEQUENTIAL);
    }

    /**
     * @param bool $isSequential
     *
     * @return static
     */
    public function setIsSequential(bool $isSequential)
    {
        return $this->setProperty(MultiInstanceLoopCharacteristicsInterface::BPMN_PROPERTY_IS_SEQUENTIAL, $isSequential);
    }

    /**
     * @return string
     */
    public function getBehavior()
    {
        return $this->getProperty(MultiInstanceLoopCharacteristicsInterface::BPMN_PROPERTY_BEHAVIOR);
    }

    /**
     * @param string $behavior
     *
     * @return static
     */
    public function setBehavior($behavior)
    {
        return $this->setProperty(MultiInstanceLoopCharacteristicsInterface::BPMN_PROPERTY_BEHAVIOR, $behavior);
    }

    /**
     * @return string
     */
    public function getOneBehaviorEventRef()
    {
        return $this->getProperty(MultiInstanceLoopCharacteristicsInterface::BPMN_PROPERTY_ONE_BEHAVIOR_EVENT_REF);
    }

    /**
     * @param string $oneBehaviorEventRef
     *
     * @return static
     */
    public function setOneBehaviorEventRef($oneBehaviorEventRef)
    {
        return $this->setProperty(MultiInstanceLoopCharacteristicsInterface::BPMN_PROPERTY_ONE_BEHAVIOR_EVENT_REF, $oneBehaviorEventRef);
    }

    /**
     * @return string
     */
    public function getNoneBehaviorEventRef()
    {
        return $this->getProperty(MultiInstanceLoopCharacteristicsInterface::BPMN_PROPERTY_NONE_BEHAVIOR_EVENT_REF);
    }

    /**
     * @param string $noneBehaviorEventRef
     *
     * @return static
     */
    public function setNoneBehaviorEventRef($noneBehaviorEventRef)
    {
        return $this->setProperty(MultiInstanceLoopCharacteristicsInterface::BPMN_PROPERTY_NONE_BEHAVIOR_EVENT_REF, $noneBehaviorEventRef);
    }

    /**
     * @return FormalExpressionInterface
     */
    public function getLoopCardinality()
    {
        return $this->getProperty(MultiInstanceLoopCharacteristicsInterface::BPMN_PROPERTY_LOOP_CARDINALITY);
    }

    /**
     * @param FormalExpressionInterface $loopCardinality
     *
     * @return static
     */
    public function setLoopCardinality(FormalExpressionInterface $loopCardinality)
    {
        return $this->setProperty(MultiInstanceLoopCharacteristicsInterface::BPMN_PROPERTY_LOOP_CARDINALITY, $loopCardinality);
    }

    /**
     * @return string
     */
    public function getLoopDataInputRef()
    {
        return $this->getProperty(MultiInstanceLoopCharacteristicsInterface::BPMN_PROPERTY_LOOP_DATA_INPUT_REF);
    }

    /**
     * @param string $loopDataInputRef
     *
     * @return static
     */
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

    /**
     * @param DataInputInterface $loopDataInput
     *
     * @return static
     */
    public function setLoopDataInput(DataInputInterface $loopDataInput)
    {
        return $this->setProperty(MultiInstanceLoopCharacteristicsInterface::BPMN_PROPERTY_LOOP_DATA_INPUT, $loopDataInput);
    }

    /**
     * @return string
     */
    public function getLoopDataOutputRef()
    {
        return $this->getProperty(MultiInstanceLoopCharacteristicsInterface::BPMN_PROPERTY_LOOP_DATA_OUTPUT_REF);
    }

    /**
     * @param string $loopDataOutputRef
     * @return void
     */
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

    /**
     * @param DataOutputInterface $loopDataOutput
     *
     * @return static
     */
    public function setLoopDataOutput(DataOutputInterface $loopDataOutput)
    {
        return $this->setProperty(MultiInstanceLoopCharacteristicsInterface::BPMN_PROPERTY_LOOP_DATA_OUTPUT, $loopDataOutput);
    }

    /**
     * @return DataInputInterface
     */
    public function getInputDataItem()
    {
        return $this->getProperty(MultiInstanceLoopCharacteristicsInterface::BPMN_PROPERTY_INPUT_DATA_ITEM);
    }

    /**
     * @param DataInputInterface $inputDataItem
     *
     * @return static
     */
    public function setInputDataItem(DataInputInterface $inputDataItem)
    {
        return $this->setProperty(MultiInstanceLoopCharacteristicsInterface::BPMN_PROPERTY_INPUT_DATA_ITEM, $inputDataItem);
    }

    /**
     * @return DataOutputInterface
     */
    public function getOutputDataItem()
    {
        return $this->getProperty(MultiInstanceLoopCharacteristicsInterface::BPMN_PROPERTY_OUTPUT_DATA_ITEM);
    }

    /**
     * @param DataOutputInterface $outputDataItem
     *
     * @return static
     */
    public function setOutputDataItem(DataOutputInterface $outputDataItem)
    {
        return $this->setProperty(MultiInstanceLoopCharacteristicsInterface::BPMN_PROPERTY_OUTPUT_DATA_ITEM, $outputDataItem);
    }

    /**
     * @return \ProcessMaker\Nayra\Contracts\Bpmn\ComplexBehaviorDefinitionInterface $complexBehaviorDefinition
     */
    public function getComplexBehaviorDefinition()
    {
        return $this->getProperty(MultiInstanceLoopCharacteristicsInterface::BPMN_PROPERTY_COMPLEX_BEHAVIOR_DEFINITION);
    }

    /**
     * @param \ProcessMaker\Nayra\Contracts\Bpmn\ComplexBehaviorDefinitionInterface $complexBehaviorDefinition
     *
     * @return static
     */
    public function setComplexBehaviorDefinition(ComplexBehaviorDefinitionInterface $complexBehaviorDefinition)
    {
        return $this->setProperty(MultiInstanceLoopCharacteristicsInterface::BPMN_PROPERTY_COMPLEX_BEHAVIOR_DEFINITION, $complexBehaviorDefinition);
    }

    /**
     * @return FormalExpressionInterface
     */
    public function getCompletionCondition()
    {
        return $this->getProperty(MultiInstanceLoopCharacteristicsInterface::BPMN_PROPERTY_COMPLETION_CONDITION);
    }

    /**
     * @param FormalExpressionInterface $completionCondition
     *
     * @return static
     */
    public function setCompletionCondition(FormalExpressionInterface $completionCondition)
    {
        return $this->setProperty(MultiInstanceLoopCharacteristicsInterface::BPMN_PROPERTY_COMPLETION_CONDITION, $completionCondition);
    }

    /**
     * Should close tokens after each loop?
     *
     * @return bool
     */
    public function shouldCloseTokensEachLoop()
    {
        return false;
    }
}
