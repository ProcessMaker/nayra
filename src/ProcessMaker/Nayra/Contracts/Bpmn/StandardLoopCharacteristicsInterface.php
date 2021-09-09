<?php

namespace ProcessMaker\Nayra\Contracts\Bpmn;

/**
 * @package ProcessMaker\Nayra\Contracts\Bpmn
 */
interface StandardLoopCharacteristicsInterface extends LoopCharacteristicsInterface
{
    const BPMN_PROPERTY_TEST_BEFORE = 'testBefore';
    const BPMN_PROPERTY_TEST_AFTER = 'testAfter';
    const BPMN_PROPERTY_LOOP_MAXIMUM = 'loopMaximum';
    const BPMN_PROPERTY_LOOP_CONDITION = 'loopCondition';
    
    /**
     * Gets as testBefore
     *
     * @return boolean
     */
    public function getTestBefore();

    /**
     * Sets a new testBefore
     *
     * @param bool $testBefore
     *
     * @return self
     */
    public function setTestBefore(bool $testBefore);

    /**
     * Gets as testAfter
     *
     * @return boolean
     */
    public function getTestAfter();

    /**
     * Sets a new testAfter
     *
     * @param bool $testAfter
     *
     * @return self
     */
    public function setTestAfter(bool $testAfter);

    /**
     * Gets as loopMaximum
     *
     * @return FormalExpressionInterface
     */
    public function getLoopMaximum();

    /**
     * Sets a new loopMaximum
     *
     * @param FormalExpressionInterface $loopMaximum
     * @return self
     */
    public function setLoopMaximum(FormalExpressionInterface $loopMaximum);

    /**
     * Gets as loopCondition
     *
     * @return FormalExpressionInterface
     */
    public function getLoopCondition();

    /**
     * Sets a new loopCondition
     *
     * @param FormalExpressionInterface $loopCondition
     * @return self
     */
    public function setLoopCondition(FormalExpressionInterface $loopCondition);
}
