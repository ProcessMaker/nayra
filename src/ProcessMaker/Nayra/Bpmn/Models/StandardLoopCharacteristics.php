<?php

namespace ProcessMaker\Nayra\Bpmn\Models;

use ProcessMaker\Nayra\Contracts\Bpmn\TokenInterface;
use ProcessMaker\Nayra\Bpmn\StandardLoopCharacteristicsTrait;
use ProcessMaker\Nayra\Contracts\Bpmn\StandardLoopCharacteristicsInterface;
use ProcessMaker\Nayra\Contracts\Engine\ExecutionInstanceInterface;

/**
 * Standard implementation.
 *
 * @package ProcessMaker\Models
 */
class StandardLoopCharacteristics extends MultiInstanceLoopCharacteristics implements StandardLoopCharacteristicsInterface
{
    use StandardLoopCharacteristicsTrait;

    /**
     * @param ExecutionInstanceInterface $instance
     * @param TokenInterface $token
     *
     * @return bool
     */
    public function continueLoop(ExecutionInstanceInterface $instance, TokenInterface $token)
    {
        return $this->checkAfterLoop($token);
    }

    /**
     * Check before the loop should be executed
     */
    public function checkBeforeLoop(TokenInterface $token)
    {
        $testBefore = $this->getTestBefore();
        $condition = $this->getLoopCondition();
        $evaluatedCondition = $condition->evaluate($token);
        $loopMaximum = $this->getLoopMaximum();
        $loopCounter = $this->getLoopCounter();
        $loopCondition = $loopMaximum === null  || $loopMaximum === 0 || $loopCounter < $loopMaximum;
        if ($testBefore && $evaluatedCondition && $loopCondition) {
            return true;
        }
        return false;
    }

    /**
     * Check after the loop should be executed
     */
    public function checkAfterLoop(TokenInterface $token)
    {
        $testBefore = $this->getTestBefore();
        $condition = $this->getLoopCondition();
        $evaluatedCondition = $condition->evaluate($token);
        $loopMaximum = $this->getLoopMaximum();
        $loopCounter = $this->getLoopCounter();
        $loopCondition = $loopMaximum === null || $loopCounter < $loopMaximum;
        if (!$testBefore && $evaluatedCondition && $loopCondition) {
            return true;
        }
        return false;
    }
}
