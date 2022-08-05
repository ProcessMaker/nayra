<?php

namespace ProcessMaker\Nayra\Contracts\Bpmn;

use ProcessMaker\Nayra\Contracts\Bpmn\FormalExpressionInterface;

interface StandardLoopCharacteristicsInterface extends LoopCharacteristicsInterface
{
    const BPMN_PROPERTY_TEST_BEFORE = 'testBefore';

    const BPMN_PROPERTY_LOOP_MAXIMUM = 'loopMaximum';

    const BPMN_PROPERTY_LOOP_CONDITION = 'loopCondition';

    /**
     * Gets as testBefore
     *
     * @return bool
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
     * Gets as loopMaximum
     *
     * @return string
     */
    public function getLoopMaximum();

    /**
     * Sets a new loopMaximum
     *
     * @param string $loopMaximum
     * @return self
     */
    public function setLoopMaximum(string $loopMaximum);

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
