<?php

namespace ProcessMaker\Nayra\Bpmn;

use ProcessMaker\Nayra\Contracts\Bpmn\FormalExpressionInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\StandardLoopCharacteristicsInterface;

/**
 * Base implementation for LoopCharacteristicsInterface
 *
 * @implements ProcessMaker\Nayra\Contracts\Bpmn\StandardLoopCharacteristicsInterface
 * 
 * @package ProcessMaker\Nayra\Bpmn
 */
trait StandardLoopCharacteristicsTrait
{
    use LoopCharacteristicsTrait;

    /**
     * @return bool
     */
    public function getTestBefore()
    {
        return $this->getProperty(StandardLoopCharacteristicsInterface::BPMN_PROPERTY_TEST_BEFORE);
    }

    /**
     * @param bool $testBefore
     *
     * 
     * @return static
     */
    public function setTestBefore(bool $testBefore)
    {
        return $this->setProperty(StandardLoopCharacteristicsInterface::BPMN_PROPERTY_TEST_BEFORE, $testBefore);
    }

    /**
     * @return string
     */
    public function getLoopMaximum()
    {
        return $this->getProperty(StandardLoopCharacteristicsInterface::BPMN_PROPERTY_LOOP_MAXIMUM);
    }

    /**
     * @param string $loopMaximum
     *
     * @return static
     */
    public function setLoopMaximum( $expression) {
        return $this->setProperty(StandardLoopCharacteristicsInterface::BPMN_PROPERTY_LOOP_MAXIMUM, $expression);
    }

    /**
     * @return FormalExpressionInterface
     */
    public function getLoopCondition()
    {
        return $this->getProperty(StandardLoopCharacteristicsInterface::BPMN_PROPERTY_LOOP_CONDITION);
    }

    /**
     * @param FormalExpressionInterface $loopCondition
     *
     * @return static
     */
    public function setLoopCondition(FormalExpressionInterface $loopCondition)
    {
        return $this->setProperty(StandardLoopCharacteristicsInterface::BPMN_PROPERTY_LOOP_CONDITION, $loopCondition);
    }
}
