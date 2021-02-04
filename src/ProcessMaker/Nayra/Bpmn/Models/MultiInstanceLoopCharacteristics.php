<?php

namespace ProcessMaker\Nayra\Bpmn\Models;

use ProcessMaker\Nayra\Bpmn\MultiInstanceLoopCharacteristicsTrait;
use ProcessMaker\Nayra\Contracts\Bpmn\CollectionInterface;
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

    public function iterateNextState(StateInterface $nextState, ExecutionInstanceInterface $instance, CollectionInterface $consumeTokens, array $properties = [], TransitionInterface $source = null)
    {
        $data = $instance->getDataStore()->getData();
        foreach ($consumeTokens as $token) {
            $properties = $this->startLoopInstanceProperty($token, $properties);
            $numberOfInstances = $this->getLoopCardinality()($data);
            if ($this->getIsSequential()) {
                $loopCounter = $this->getLoopInstanceProperty($token, 0);
                if ($loopCounter < $numberOfInstances) {
                    $loopCounter++;
                    $newToken = $nextState->addNewToken($instance, $properties, $source);
                    $this->setLoopInstanceProperty($newToken, 'numberOfInstances', $numberOfInstances);
                    $this->setLoopInstanceProperty($newToken, 'loopCounter', $loopCounter);
                }
            } else {
                for ($loopCounter = 1; $loopCounter <= $numberOfInstances; $loopCounter++) {
                    $newToken = $nextState->addNewToken($instance, $properties, $source);
                    $this->setLoopInstanceProperty($newToken, 'numberOfInstances', $numberOfInstances);
                    $this->setLoopInstanceProperty($newToken, 'loopCounter', $loopCounter);
                }
            }
        }
    }

    public function isLoopCompleted(ExecutionInstanceInterface $instance, TokenInterface $token)
    {
        $data = $instance->getDataStore()->getData();
        $numberOfInstances = $this->getLoopCardinality()($data);
        $completed = $this->getLoopInstanceProperty($token, 'numberOfCompletedInstances', 0);
        return $completed >= $numberOfInstances;
    }

    public function onTokenCompleted(TokenInterface $token)
    {
        $numberOfCompletedInstances = $this->getLoopInstanceProperty($token, 'numberOfCompletedInstances', 0);
        $numberOfCompletedInstances++;
        $this->setLoopInstanceProperty($token, 'numberOfCompletedInstances', $numberOfCompletedInstances);
    }

    public function onTokenTerminated(TokenInterface $token)
    {
        $numberOfTerminatedInstances = $this->getLoopInstanceProperty($token, 'numberOfTerminatedInstances', 0);
        $numberOfTerminatedInstances++;
        $this->setLoopInstanceProperty($token, 'numberOfTerminatedInstances', $numberOfTerminatedInstances);
    }
}
