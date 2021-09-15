<?php

namespace ProcessMaker\Nayra\Bpmn\Models;

use ProcessMaker\Nayra\Contracts\Bpmn\StateInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\TokenInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\CollectionInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\TransitionInterface;
use ProcessMaker\Nayra\Bpmn\StandardLoopCharacteristicsTrait;
use ProcessMaker\Nayra\Contracts\Bpmn\FormalExpressionInterface;
use ProcessMaker\Nayra\Contracts\Engine\ExecutionInstanceInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\StandardLoopCharacteristicsInterface;

/**
 * Standard implementation.
 *
 * @package ProcessMaker\Models
 */
class StandardLoopCharacteristics implements StandardLoopCharacteristicsInterface
{
    use StandardLoopCharacteristicsTrait;

    /**
     * Check if the loop can be formally executed
     *
     * @return boolean
     */
    public function isExecutable()
    {
        $loopMaximumValid = false;
        $loopConditionValid = false;
        $loopMaximum = $this->getLoopMaximum();
        $loopCondition = $this->getLoopCondition();

        if ($loopMaximum) {
            $loopMaximumValid = true;
        }

        if ($loopCondition && $loopCondition->getBody()) {
            $loopConditionValid = true;
        }

        return $loopMaximumValid || $loopConditionValid;
    }

    /**
     * Get item of the input data collection by index
     *
     * @param ExecutionInstanceInterface $instance
     * @param integer $index
     *
     * @return mixed
     */
    private function getInputDataItemValue(ExecutionInstanceInterface $instance, $index)
    {
        $dataStore = $instance->getDataStore();
        return $dataStore;
    }

    /**
     * Check if data input is valid
     *
     * @param ExecutionInstanceInterface $instance
     * @param TokenInterface $token
     *
     * @return bool
     */
    public function isDataInputValid(ExecutionInstanceInterface $instance, TokenInterface $token)
    {
        // todo: check if data input is valid
        return true;
    }

    /**
     * @param TokenInterface $token
     *
     * @return void
     */
    public function onTokenCompleted(TokenInterface $token)
    {
        //required for internal validation
    }

    /**
     * Merge output data into instance data
     *
     * @param CollectionInterface $consumedTokens
     * @param ExecutionInstanceInterface $instance
     *
     * @return void
     */
    public function mergeOutputData(CollectionInterface $consumedTokens, ExecutionInstanceInterface $instance)
    {
        //required for internal validation
    }

    /**
     * Iterate to next active state
     *
     * @param StateInterface $nextState
     * @param ExecutionInstanceInterface $instance
     * @param CollectionInterface $consumeTokens
     * @param array $properties
     * @param TransitionInterface|null $source
     *
     * @return void
     */
    public function iterateNextState(StateInterface $nextState, ExecutionInstanceInterface $instance, CollectionInterface $consumeTokens, array $properties = [], TransitionInterface $source = null)
    {
        foreach ($consumeTokens as $token) {
            $properties = $this->prepareLoopInstanceProperties($token, $properties);
            // LoopInstance Counter
            $loopCounter = $this->getLoopInstanceProperty($token, 'loopCounter', 0);
            // Token loopCounter
            $tokenLoopCounter = $token->getProperty('data', [])['loopCounter'] ?? 0;
            if ($loopCounter === $tokenLoopCounter) {
                $loopCounter++;
                $this->createInstance(
                    $instance,
                    $properties,
                    $loopCounter,
                    $nextState,
                    $source
                );
            }
        }
    }

    /**
     * @param ExecutionInstanceInterface $instance
     * @param array $properties
     * @param integer $loopCounter
     * @param StateInterface $nextState
     * @param TransitionInterface $source
     * @return void
     */
    private function createInstance(
        ExecutionInstanceInterface $instance,
        array $properties,
        $loopCounter,
        StateInterface $nextState,
        TransitionInterface $source
    ) {
        $item = $this->getInputDataItemValue($instance, $loopCounter);
        $properties['data'] = [];
        $properties['data'] = array_merge($properties['data'], (array) $item);
        $properties['data']['loopCounter'] = $loopCounter;
        $newToken = $nextState->createToken($instance, $properties, $source);
        $this->setLoopInstanceProperty($newToken, 'loopCounter', $loopCounter);
        $this->setLoopInstanceProperty($newToken, 'nextState', $nextState);
        $this->setLoopInstanceProperty($newToken, 'source', $source);
        $nextState->addToken($instance, $newToken, false, $source);
    }

    /**
     * @param ExecutionInstanceInterface $instance
     * @param TokenInterface $token
     *
     * @return bool
     */
    public function continueLoop(ExecutionInstanceInterface $instance, TokenInterface $token)
    {
        if ($this->getTestBefore()) {
            $continue = $this->checkBeforeLoop($instance, $token);
        } else {
            $continue = $this->checkAfterLoop($instance, $token);
        }
        if ($continue) {
            $properties = $this->prepareLoopInstanceProperties($token, []);
            // LoopInstance Counter
            $loopCounter = $this->getLoopInstanceProperty($token, 'loopCounter', 0);
            // Token loopCounter
            $tokenLoopCounter = $token->getProperty('data', [])['loopCounter'] ?? 0;
            if ($loopCounter === $tokenLoopCounter) {
                $loopCounter++;
                $this->createInstance(
                    $instance,
                    $properties,
                    $loopCounter,
                    $this->getLoopInstanceProperty($token, 'nextState'),
                    $this->getLoopInstanceProperty($token, 'source')
                );
            }
        }
        return $continue;
    }

    /**
     * @param ExecutionInstanceInterface $instance
     * @param TokenInterface $token
     * @return bool
     */
    public function isLoopCompleted(ExecutionInstanceInterface $instance, TokenInterface $token)
    {
        if ($this->getTestBefore()) {
            return !$this->checkBeforeLoop($instance, $token);
        }
        return !$this->checkAfterLoop($instance, $token);
    }

    /**
     * Check before the loop should be executed
     *
     * @param  ExecutionInstanceInterface $instance
     * @param  TokenInterface $token
     * @return bool
     */
    private function checkBeforeLoop(ExecutionInstanceInterface $instance, TokenInterface $token)
    {
        $testBefore = $this->getTestBefore();
        $condition = $this->getLoopCondition();
        $data = $instance->getDataStore()->getData();
        $evaluatedCondition = $condition($data);
        $loopMaximum = $this->getLoopMaximumFormalExpression($data);
        $loopCounter = $this->getLoopInstanceProperty($token, 'loopCounter', 0);
        $loopCondition = $loopMaximum === null  || $loopMaximum === 0 || $loopCounter < $loopMaximum;
        if ($testBefore && $evaluatedCondition && $loopCondition) {
            return true;
        }
        return false;
    }

    /**
     * Check after the loop should be executed
     *
     * @param  ExecutionInstanceInterface $instance
     * @param  TokenInterface $token
     * @return bool
     */
    private function checkAfterLoop(ExecutionInstanceInterface $instance, TokenInterface $token)
    {
        $testBefore = $this->getTestBefore();
        $condition = $this->getLoopCondition();
        $data = $instance->getDataStore()->getData();
        $evaluatedCondition = $condition($data);
        $loopMaximum = $this->getLoopMaximumFormalExpression($data);
        $loopCounter = $this->getLoopInstanceProperty($token, 'loopCounter', 0);
        $loopCondition = $loopMaximum === null || $loopCounter < $loopMaximum;
        if (!$testBefore && $loopCounter === 0) {
            return true;
        }
        if (!$testBefore && $evaluatedCondition && $loopCondition) {
            return true;
        }
        return false;
    }

    /**
     * getLoopMaximumFormalExpression
     *
     * @param  array $data
     * @return int
     */
    private function getLoopMaximumFormalExpression(array $data)
    {
        $expression = $this->getLoopMaximum();
        $formalExpression = $this->getRepository()->createFormalExpression();
        $formalExpression->setProperty(FormalExpressionInterface::BPMN_PROPERTY_BODY, $expression);
        return $formalExpression($data);
    }
}
