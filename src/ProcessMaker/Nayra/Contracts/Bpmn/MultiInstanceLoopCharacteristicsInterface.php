<?php

namespace ProcessMaker\Nayra\Contracts\Bpmn;

interface MultiInstanceLoopCharacteristicsInterface extends LoopCharacteristicsInterface
{
    const BPMN_PROPERTY_IS_SEQUENTIAL = 'isSequential';
    const BPMN_PROPERTY_BEHAVIOR = 'behavior';
    const BPMN_PROPERTY_LOOP_CARDINALITY = 'loopCardinality';
    const BPMN_PROPERTY_ONE_BEHAVIOR_EVENT_REF = 'oneBehaviorEventRef';
    const BPMN_PROPERTY_NONE_BEHAVIOR_EVENT_REF = 'oneBehaviorEventRef';
    const BPMN_PROPERTY_LOOP_DATA_INPUT_REF = 'loopDataInputRef';
    const BPMN_PROPERTY_LOOP_DATA_OUTPUT_REF = 'loopDataOutputRef';
    const BPMN_PROPERTY_LOOP_DATA_INPUT = 'loopDataInputRef';
    const BPMN_PROPERTY_LOOP_DATA_OUTPUT = 'loopDataOutputRef';
    const BPMN_PROPERTY_INPUT_DATA_ITEM = 'inputDataItem';
    const BPMN_PROPERTY_OUTPUT_DATA_ITEM = 'outputDataItem';
    const BPMN_PROPERTY_COMPLEX_BEHAVIOR_DEFINITION = 'complexBehaviorDefinition';
    const BPMN_PROPERTY_COMPLETION_CONDITION = 'completionCondition';

    /**
     * Gets as isSequential
     *
     * @return boolean
     */
    public function getIsSequential();

    /**
     * Sets a new isSequential
     *
     * @param boolean $isSequential
     * @return self
     */
    public function setIsSequential(bool $isSequential);

    /**
     * Gets as behavior
     *
     * @return string
     */
    public function getBehavior();

    /**
     * Sets a new behavior
     *
     * @param string $behavior
     * @return self
     */
    public function setBehavior($behavior);

    /**
     * Gets as oneBehaviorEventRef
     *
     * @return string
     */
    public function getOneBehaviorEventRef();

    /**
     * Sets a new oneBehaviorEventRef
     *
     * @param string $oneBehaviorEventRef
     * @return self
     */
    public function setOneBehaviorEventRef($oneBehaviorEventRef);

    /**
     * Gets as noneBehaviorEventRef
     *
     * @return string
     */
    public function getNoneBehaviorEventRef();

    /**
     * Sets a new noneBehaviorEventRef
     *
     * @param string $noneBehaviorEventRef
     * @return self
     */
    public function setNoneBehaviorEventRef($noneBehaviorEventRef);

    /**
     * Gets as loopCardinality
     *
     * @return FormalExpressionInterface
     */
    public function getLoopCardinality();

    /**
     * Sets a new loopCardinality
     *
     * @param FormalExpressionInterface $loopCardinality
     * @return self
     */
    public function setLoopCardinality(FormalExpressionInterface $loopCardinality);

    /**
     * Gets as loopDataInputRef
     *
     * @return string
     */
    public function getLoopDataInputRef();

    /**
     * Sets a new loopDataInputRef
     *
     * @param string $loopDataInputRef
     * @return self
     */
    public function setLoopDataInputRef($loopDataInputRef);

    /**
     * Gets as loopDataInputRef
     *
     * @return DataInputInterface
     */
    public function getLoopDataInput();

    /**
     * Sets a new loopDataInputRef
     *
     * @param DataInputInterface $loopDataInput
     * @return self
     */
    public function setLoopDataInput(DataInputInterface $loopDataInput);

    /**
     * Gets as loopDataOutputRef
     *
     * @return string
     */
    public function getLoopDataOutputRef();

    /**
     * Sets a new loopDataOutputRef
     *
     * @param string $loopDataOutputRef
     * @return self
     */
    public function setLoopDataOutputRef($loopDataOutputRef);

    /**
     * Gets as loopDataOutputRef
     *
     * @return DataOutputInterface
     */
    public function getLoopDataOutput();

    /**
     * Sets a new loopDataOutputRef
     *
     * @param string $loopDataOutput
     * @return self
     */
    public function setLoopDataOutput(DataOutputInterface $loopDataOutput);

    /**
     * Gets as inputDataItem
     *
     * @return DataInputInterface
     */
    public function getInputDataItem();

    /**
     * Sets a new inputDataItem
     *
     * @param DataInputInterface $inputDataItem
     * @return self
     */
    public function setInputDataItem(DataInputInterface $inputDataItem);

    /**
     * Gets as outputDataItem
     *
     * @return DataOutputInterface
     */
    public function getOutputDataItem();

    /**
     * Sets a new outputDataItem
     *
     * @param DataOutputInterface $outputDataItem
     * @return self
     */
    public function setOutputDataItem(DataOutputInterface $outputDataItem);

    /**
     * Gets as complexBehaviorDefinition
     *
     * @return array
     */
    public function getComplexBehaviorDefinition();

    /**
     * Sets a new complexBehaviorDefinition
     *
     * @param array $complexBehaviorDefinition
     * @return self
     */
    public function setComplexBehaviorDefinition($complexBehaviorDefinition);

    /**
     * Gets as completionCondition
     *
     * @return FormalExpressionInterface
     */
    public function getCompletionCondition();

    /**
     * Sets a new completionCondition
     *
     * @param FormalExpressionInterface $completionCondition
     * @return self
     */
    public function setCompletionCondition(FormalExpressionInterface $completionCondition);
}
