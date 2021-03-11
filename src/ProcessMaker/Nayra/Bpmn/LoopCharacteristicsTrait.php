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

    /**
     * Prepare Loop Instance properties for execution
     *
     * @param TokenInterface $token
     * @param array $properties
     *
     * @return array
     */
    private function prepareLoopInstanceProperties(TokenInterface $token, array $properties = [])
    {
        $loopCharacteristics = $token->getProperty(
            LoopCharacteristicsInterface::BPMN_LOOP_INSTANCE_PROPERTY,
            $properties[LoopCharacteristicsInterface::BPMN_LOOP_INSTANCE_PROPERTY] ?? []
        );
        if (empty($loopCharacteristics['sourceToken'])) {
            $loopCharacteristics['sourceToken'] = $token->getId();
        }
        $properties[LoopCharacteristicsInterface::BPMN_LOOP_INSTANCE_PROPERTY] = $loopCharacteristics;
        $token->setProperty(
            LoopCharacteristicsInterface::BPMN_LOOP_INSTANCE_PROPERTY,
            $properties[LoopCharacteristicsInterface::BPMN_LOOP_INSTANCE_PROPERTY]
        );
        return $properties;
    }

    /**
     * Set Loop Instance property during execution
     *
     * @param TokenInterface $token
     * @param string $key
     * @param mixed $value
     *
     * @return self
     */
    private function setLoopInstanceProperty(TokenInterface $token, $key, $value)
    {
        $loopCharacteristics = $token->getProperty(LoopCharacteristicsInterface::BPMN_LOOP_INSTANCE_PROPERTY, []);
        $outerInstance = $loopCharacteristics['sourceToken'];
        $ds = $token->getInstance()->getDataStore();
        $data = $ds->getData(LoopCharacteristicsInterface::BPMN_LOOP_INSTANCE_PROPERTY, []);
        $data[$outerInstance] = $data[$outerInstance] ?? [];
        $data[$outerInstance][$key] = $value;
        $ds->putData(LoopCharacteristicsInterface::BPMN_LOOP_INSTANCE_PROPERTY, $data);
        return $this;
    }

    /**
     * Get Loop Instance property during execution
     *
     * @param TokenInterface $token
     * @param string $key
     * @param mixed $defaultValue
     *
     * @return mixed
     */
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
