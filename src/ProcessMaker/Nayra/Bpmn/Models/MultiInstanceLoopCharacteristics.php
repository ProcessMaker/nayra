<?php

namespace ProcessMaker\Nayra\Bpmn\Models;

use Countable;
use ProcessMaker\Nayra\Bpmn\MultiInstanceLoopCharacteristicsTrait;
use ProcessMaker\Nayra\Contracts\Bpmn\CollectionInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\DataInputInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\DataStoreInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\MultiInstanceLoopCharacteristicsInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\StateInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\TokenInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\TransitionInterface;
use ProcessMaker\Nayra\Contracts\Engine\ExecutionInstanceInterface;

/**
 * Multiinstance implementation.
 *
 * @package ProcessMaker\Models
 */
class MultiInstanceLoopCharacteristics implements MultiInstanceLoopCharacteristicsInterface
{
    use MultiInstanceLoopCharacteristicsTrait;

    /**
     * Calculate the number of intances for the MI activity
     *
     * @param ExecutionInstanceInterface $instance
     *
     * @return integer
     */
    private function calcNumberOfInstances(ExecutionInstanceInterface $instance)
    {
        $dataStore = $instance->getDataStore();
        $loopCardinality = $this->getLoopCardinality();
        $loopDataInput = $this->getLoopDataInput();
        if ($loopCardinality) {
            return $loopCardinality($dataStore->getData());
        } else {
            return count($this->getInputDataValue($loopDataInput, $dataStore));
        }
    }

    /**
     * Check if the loop can be formally executed
     *
     * @return boolean
     */
    public function isExecutable()
    {
        $loopCardinality = $this->getLoopCardinality();
        $loopDataInput = $this->getLoopDataInput();
        return $loopCardinality || $loopDataInput;
    }

    /**
     * Get input data runtime value
     *
     * @param DataInputInterface $dataInput
     * @param DataStoreInterface $dataStore
     *
     * @return CollectionInterface|array
     */
    private function getInputDataValue(DataInputInterface $dataInput, DataStoreInterface $dataStore)
    {
        return $dataStore->getData($dataInput->getName(), []);
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
        $dataInput = $this->getLoopDataInput();
        if (!$dataInput) {
            return null;
        }
        return $this->getInputDataValue($dataInput, $dataStore)[$index - 1];
    }

    /**
     * Get item of the output data from token
     *
     * @param TokenInterface $token
     *
     * @return array
     */
    private function getOutputDataItemValue(TokenInterface $token)
    {
        $data = $token->getProperty('data', []);
        $name = $this->getOutputDataItem() ? $this->getOutputDataItem()->getName() : null;
        if ($name) {
            return $data[$name] ?? null;
        }
        return $data;
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
        $inputDataItem = $this->getInputDataItem() ? $this->getInputDataItem()->getName() : null;
        foreach ($consumeTokens as $token) {
            $properties = $this->prepareLoopInstanceProperties($token, $properties);
            // The number of instances are calculated once, when entering the activity.
            $numberOfInstances = $this->calcNumberOfInstances($instance);
            if ($this->getIsSequential()) {
                // LoopInstance Counter
                $loopCounter = $this->getLoopInstanceProperty($token, 'loopCounter', 0);
                // Token loopCounter
                $tokenLoopCounter = $token->getProperty('data', [])['loopCounter'] ?? 0;
                if ($loopCounter === $tokenLoopCounter && $loopCounter < $numberOfInstances) {
                    $loopCounter++;
                    $numberOfActiveInstances = 1;
                    $this->createInstance(
                        $instance,
                        $properties,
                        $loopCounter,
                        $inputDataItem,
                        $nextState,
                        $source,
                        $numberOfActiveInstances,
                        $numberOfInstances
                    );
                }
            } else {
                $numberOfActiveInstances = $numberOfInstances;
                for ($loopCounter = 1; $loopCounter <= $numberOfInstances; $loopCounter++) {
                    $this->createInstance(
                        $instance,
                        $properties,
                        $loopCounter,
                        $inputDataItem,
                        $nextState,
                        $source,
                        $numberOfActiveInstances,
                        $numberOfInstances
                    );
                }
            }
        }
    }

    /**
     * @param ExecutionInstanceInterface $instance
     * @param array $properties
     * @param integer $loopCounter
     * @param string $inputDataItem
     * @param StateInterface $nextState
     * @param TransitionInterface $source
     * @param integer $numberOfActiveInstances
     * @param integer $numberOfInstances
     * @return void
     */
    private function createInstance(
        ExecutionInstanceInterface $instance,
        array $properties,
        $loopCounter,
        $inputDataItem,
        StateInterface $nextState,
        TransitionInterface $source,
        $numberOfActiveInstances,
        $numberOfInstances
    ) {
        $item = $this->getInputDataItemValue($instance, $loopCounter);
        $properties['data'] = [];
        if ($inputDataItem) {
            $properties['data'][$inputDataItem] = $item;
        } elseif ($item) {
            $properties['data'] = array_merge($properties['data'], (array) $item);
        }
        $properties['data']['loopCounter'] = $loopCounter;
        $newToken = $nextState->createToken($instance, $properties, $source);
        $this->setLoopInstanceProperty($newToken, 'numberOfActiveInstances', $numberOfActiveInstances);
        $this->setLoopInstanceProperty($newToken, 'numberOfInstances', $numberOfInstances);
        $this->setLoopInstanceProperty($newToken, 'loopCounter', $loopCounter);
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
        $numberOfInstances = $this->getLoopInstanceProperty($token, 'numberOfInstances', 0);
        $active = $this->getLoopInstanceProperty($token, 'numberOfActiveInstances', 0);
        $completed = $this->getLoopInstanceProperty($token, 'numberOfCompletedInstances', 0);
        $terminated = $this->getLoopInstanceProperty($token, 'numberOfTerminatedInstances', 0);
        $total = $active + $completed + $terminated;
        return $total < $numberOfInstances;
    }

    /**
     * @param ExecutionInstanceInterface $instance
     * @param TokenInterface $token
     * @return bool
     */
    public function isLoopCompleted(ExecutionInstanceInterface $instance, TokenInterface $token)
    {
        $numberOfInstances = $this->getLoopInstanceProperty($token, 'numberOfInstances', null);
        if ($numberOfInstances === null) {
            $numberOfInstances = $this->calcNumberOfInstances($instance);
        }
        $completed = $this->getLoopInstanceProperty($token, 'numberOfCompletedInstances', 0);
        return $completed >= $numberOfInstances;
    }

    /**
     * @param TokenInterface $token
     *
     * @return void
     */
    public function onTokenCompleted(TokenInterface $token)
    {
        $numberOfActiveInstances = $this->getLoopInstanceProperty($token, 'numberOfActiveInstances', 0);
        $numberOfCompletedInstances = $this->getLoopInstanceProperty($token, 'numberOfCompletedInstances', 0);
        $numberOfActiveInstances--;
        $numberOfCompletedInstances++;
        $this->setLoopInstanceProperty($token, 'numberOfActiveInstances', $numberOfActiveInstances);
        $this->setLoopInstanceProperty($token, 'numberOfCompletedInstances', $numberOfCompletedInstances);
    }

    /**
     * @param TokenInterface $token
     *
     * @return void
     */
    public function onTokenTerminated(TokenInterface $token)
    {
        $numberOfActiveInstances = $this->getLoopInstanceProperty($token, 'numberOfActiveInstances', 0);
        $numberOfTerminatedInstances = $this->getLoopInstanceProperty($token, 'numberOfTerminatedInstances', 0);
        $numberOfActiveInstances--;
        $numberOfTerminatedInstances++;
        $this->setLoopInstanceProperty($token, 'numberOfActiveInstances', $numberOfActiveInstances);
        $this->setLoopInstanceProperty($token, 'numberOfTerminatedInstances', $numberOfTerminatedInstances);
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
        $outputVariable = $this->getLoopDataOutput() ? $this->getLoopDataOutput()->getName() : null;
        if ($outputVariable) {
            $result = [];
            foreach ($consumedTokens as $token) {
                $result[] = $this->getOutputDataItemValue($token);
            }
            $instance->getDataStore()->putData($outputVariable, $result);
        }
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
        $dataStore = $instance->getDataStore();
        $loopCardinality = $this->getLoopCardinality();
        $loopDataInput = $this->getLoopDataInput();
        if ($loopCardinality) {
            $cardinality = $loopCardinality($dataStore->getData());
            return \is_numeric($cardinality) && $cardinality > 0;
        } else {
            $dataInput = $this->getInputDataValue($loopDataInput, $dataStore);
            $isCountable = is_array($dataInput) || $dataInput instanceof Countable;
            if (!$isCountable) {
                return false;
            }
            $count = \count($dataInput);
            $isSequentialArray = array_keys($dataInput) === \range(0, $count - 1);
            if (!$isSequentialArray || $count === 0) {
                return false;
            }
            return true;
        }
    }

    /**
     * Check if data input is valid
     *
     * @param ExecutionInstanceInterface $instance
     * @param TokenInterface $token
     *
     * @return string
     */
    public function getDataInputError(ExecutionInstanceInterface $instance, TokenInterface $token)
    {
        $dataStore = $instance->getDataStore();
        $loopCardinality = $this->getLoopCardinality();
        $loopDataInput = $this->getLoopDataInput();
        if ($loopCardinality) {
            $cardinality = $loopCardinality($dataStore->getData());
            if (!\is_numeric($cardinality) && $cardinality >=0) {
                return  "Invalid data input, expected a number";
            }
        } else {
            $loopDataInputName = $loopDataInput->getName();
            $dataInput = $this->getInputDataValue($loopDataInput, $dataStore);
            $isCountable = is_array($dataInput) || $dataInput instanceof Countable;
            if (!$isCountable) {
                return "Invalid data input ({$loopDataInputName}), it must be a sequential array";
            }
            $count = \count($dataInput);
            $isSequentialArray = $count ===0 || array_keys($dataInput) === \range(0, $count - 1);
            if (!$isSequentialArray) {
                return "The data input ({$loopDataInputName}) is an object or an associative array, it must be a sequential array";
            }
        }
        return '';
    }
}
