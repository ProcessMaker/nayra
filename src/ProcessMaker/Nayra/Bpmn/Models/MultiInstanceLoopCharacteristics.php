<?php

namespace ProcessMaker\Nayra\Bpmn\Models;

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

    private function calcNumberOfInstances(ExecutionInstanceInterface $instance)
    {
        $dataStore = $instance->getDataStore();
        $loopCardinality = $this->getLoopCardinality();
        $loopDataInput = $this->getLoopDataInput();
        if ($loopCardinality) {
            return $loopCardinality($dataStore->getData());
        } elseif ($loopDataInput) {
            return count($this->getInputDataValue($loopDataInput, $dataStore));
        }
    }

    private function getInputDataValue(DataInputInterface $dataInput, DataStoreInterface $dataStore)
    {
        return $dataStore->getData($dataInput->getName(), []);
    }

    private function getInputDataItemValue(ExecutionInstanceInterface $instance, $index)
    {
        $dataStore = $instance->getDataStore();
        $dataInput = $this->getLoopDataInput();
        if (!$dataInput) {
            return null;
        }
        return $this->getInputDataValue($dataInput, $dataStore)[$index - 1];
    }

    public function iterateNextState(StateInterface $nextState, ExecutionInstanceInterface $instance, CollectionInterface $consumeTokens, array $properties = [], TransitionInterface $source = null)
    {
        $inputDataItem = $this->getInputDataItem() ? $this->getInputDataItem()->getName() : null;
        foreach ($consumeTokens as $token) {
            $properties = $this->startLoopInstanceProperty($token, $properties);
            // The number of instances are calculated once, when entering the activity.
            $numberOfInstances = $this->calcNumberOfInstances($instance);
            if ($this->getIsSequential()) {
                $loopCounter = $this->getLoopInstanceProperty($token, 'loopCounter', 0);
                if ($loopCounter < $numberOfInstances) {
                    $loopCounter++;
                    $numberOfActiveInstances = 1;
                    $item = $this->getInputDataItemValue($instance, $loopCounter);
                    $properties['data'] = [
                        'loopCounter' => $loopCounter,
                    ];
                    if ($inputDataItem) {
                        $properties['data'][$inputDataItem] = $item;
                    }
                    $newToken = $nextState->addNewToken($instance, $properties, $source);
                    $this->setLoopInstanceProperty($newToken, 'numberOfActiveInstances', $numberOfActiveInstances);
                    $this->setLoopInstanceProperty($newToken, 'numberOfInstances', $numberOfInstances);
                    $this->setLoopInstanceProperty($newToken, 'loopCounter', $loopCounter);
                }
            } else {
                $numberOfActiveInstances = $numberOfInstances;
                for ($loopCounter = 1; $loopCounter <= $numberOfInstances; $loopCounter++) {
                }
            }
        }
    }

    private function createInstance(ExecutionInstanceInterface $instance, int $loopCounter, $inputDataItem, $nextState, $source, $numberOfActiveInstances, $numberOfInstances)
    {
        $item = $this->getInputDataItemValue($instance, $loopCounter);
        $properties['data'] = [
            'loopCounter' => $loopCounter,
        ];
        if ($inputDataItem) {
            $properties['data'][$inputDataItem] = $item;
        }
        $newToken = $nextState->addNewToken($instance, $properties, $source);
        $this->setLoopInstanceProperty($newToken, 'numberOfActiveInstances', $numberOfActiveInstances);
        $this->setLoopInstanceProperty($newToken, 'numberOfInstances', $numberOfInstances);
        $this->setLoopInstanceProperty($newToken, 'loopCounter', $loopCounter);
    }

    public function continueLoop(ExecutionInstanceInterface $instance, TokenInterface $token)
    {
        $numberOfInstances = $this->getLoopInstanceProperty($token, 'numberOfInstances', 0);
        $active = $this->getLoopInstanceProperty($token, 'numberOfActiveInstances', 0);
        $completed = $this->getLoopInstanceProperty($token, 'numberOfCompletedInstances', 0);
        $terminated = $this->getLoopInstanceProperty($token, 'numberOfTerminatedInstances', 0);
        $total = $active + $completed + $terminated;
        return $total < $numberOfInstances;
    }

    public function isLoopCompleted(ExecutionInstanceInterface $instance, TokenInterface $token)
    {
        $data = $instance->getDataStore()->getData();
        $numberOfInstances = $this->getLoopInstanceProperty($token, 'numberOfInstances', 0);
        $completed = $this->getLoopInstanceProperty($token, 'numberOfCompletedInstances', 0);
        return $completed >= $numberOfInstances;
    }

    public function onTokenCompleted(TokenInterface $token)
    {
        $numberOfActiveInstances = $this->getLoopInstanceProperty($token, 'numberOfActiveInstances', 0);
        $numberOfCompletedInstances = $this->getLoopInstanceProperty($token, 'numberOfCompletedInstances', 0);
        $numberOfActiveInstances--;
        $numberOfCompletedInstances++;
        $this->setLoopInstanceProperty($token, 'numberOfActiveInstances', $numberOfActiveInstances);
        $this->setLoopInstanceProperty($token, 'numberOfCompletedInstances', $numberOfCompletedInstances);
    }

    public function onTokenTerminated(TokenInterface $token)
    {
        $numberOfActiveInstances = $this->getLoopInstanceProperty($token, 'numberOfActiveInstances', 0);
        $numberOfTerminatedInstances = $this->getLoopInstanceProperty($token, 'numberOfTerminatedInstances', 0);
        $numberOfActiveInstances--;
        $numberOfTerminatedInstances++;
        $this->setLoopInstanceProperty($token, 'numberOfActiveInstances', $numberOfActiveInstances);
        $this->setLoopInstanceProperty($token, 'numberOfTerminatedInstances', $numberOfTerminatedInstances);
    }
}
