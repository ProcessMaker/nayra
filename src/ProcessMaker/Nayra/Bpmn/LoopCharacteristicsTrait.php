<?php

namespace ProcessMaker\Nayra\Bpmn;

use ProcessMaker\Nayra\Contracts\Bpmn\LoopCharacteristicsInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\TokenInterface;

/**
 * Base implementation for LoopCharacteristicsInterface
 *
 * @package ProcessMaker\Nayra\Bpmn
 */
trait LoopCharacteristicsTrait
{
    use BaseTrait;

    private function startLoopInstanceProperty(TokenInterface $token, array $properties = [])
    {
        $loopCharacteristics = $token->getProperty(
            LoopCharacteristicsInterface::BPMN_LOOP_INSTANCE_PROPERTY,
            $properties[LoopCharacteristicsInterface::BPMN_LOOP_INSTANCE_PROPERTY] ?? []
        );
        if (empty($loopCharacteristics['sourceToken'])) {
            $loopCharacteristics['sourceToken'] = $token->getId();
        }
        $properties[LoopCharacteristicsInterface::BPMN_LOOP_INSTANCE_PROPERTY] = $loopCharacteristics;
        return $properties;
    }

    private function setLoopInstanceProperty(TokenInterface $token, $key, $value)
    {
        $loopCharacteristics = $token->getProperty(LoopCharacteristicsInterface::BPMN_LOOP_INSTANCE_PROPERTY, []);
        $outerInstance = $loopCharacteristics['sourceToken'];
        $ds = $token->getInstance()->getDataStore();
        $data = $ds->getData(LoopCharacteristicsInterface::BPMN_LOOP_INSTANCE_PROPERTY, []);
        $data[$outerInstance] = $data[$outerInstance] ?? [];
        $data[$outerInstance][$key] = $value;
        $ds->putData(LoopCharacteristicsInterface::BPMN_LOOP_INSTANCE_PROPERTY, $data);
    }

    public function getLoopInstanceProperty(TokenInterface $token, $key, $defaultValue = null)
    {
        $loopCharacteristics = $token->getProperty(LoopCharacteristicsInterface::BPMN_LOOP_INSTANCE_PROPERTY, []);
        $outerInstance = $loopCharacteristics['sourceToken'];
        $ds = $token->getInstance()->getDataStore();
        $data = $ds->getData(LoopCharacteristicsInterface::BPMN_LOOP_INSTANCE_PROPERTY, []);
        $data[$outerInstance] = $data[$outerInstance] ?? [];
        return $data[$outerInstance][$key] ?? $defaultValue;
    }
}
