<?php

namespace ProcessMaker\Models;

use ProcessMaker\Nayra\Bpmn\FlowTrait;
use ProcessMaker\Nayra\Contracts\Bpmn\FlowInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\FlowNodeInterface;

/**
 * Flow implementation.
 *
 * @package ProcessMaker\Models
 *
 */
class Flow implements FlowInterface
{
    use FlowTrait,
        LocalPropertiesTrait,
        LocalProcessTrait;

    /**
     * @return FlowNodeInterface
     */
    public function getSource()
    {
        return $this->getProperty(FlowInterface::BPMN_PROPERTY_SOURCE);
    }

    /**
     * @param FlowNodeInterface $source
     *
     * @return $this
     */
    public function setSource(FlowNodeInterface $source)
    {
        $this->setProperty(FlowInterface::BPMN_PROPERTY_SOURCE, $source);
        return $this;
    }

    /**
     * @return FlowNodeInterface
     */
    public function getTarget()
    {
        return $this->getProperty(FlowInterface::BPMN_PROPERTY_TARGET);
    }

    /**
     * @param FlowNodeInterface $target
     *
     * @return $this
     */
    public function setTarget(FlowNodeInterface $target)
    {
        $this->setProperty(FlowInterface::BPMN_PROPERTY_TARGET, $target);
        return $this;
    }

    /**
     * @return callable
     */
    public function getCondition()
    {
        return $this->getProperty(FlowInterface::BPMN_PROPERTY_CONDITION_EXPRESSION, function () {
            return true;
        });
    }

    /**
     * @return bool
     */
    public function isDefault()
    {
        return $this->getProperty(FlowInterface::BPMN_PROPERTY_IS_DEFAULT, false);
    }

    /**
     * @return bool
     */
    public function hasCondition()
    {
        return $this->getProperty(FlowInterface::BPMN_PROPERTY_CONDITION_EXPRESSION, null) !== null;
    }
}