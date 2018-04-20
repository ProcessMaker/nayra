<?php

namespace ProcessMaker\Models;

use ProcessMaker\Nayra\Bpmn\FlowTrait;
use ProcessMaker\Nayra\Contracts\Bpmn\FlowInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\FlowNodeInterface;

/**
 * Flow implementation.
 *
 * @package ProcessMaker\Models
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
        return $this->getProperty('source');
    }

    /**
     * @param FlowNodeInterface $source
     *
     * @return $this
     */
    public function setSource(FlowNodeInterface $source)
    {
        $this->setProperty('source', $source);
        return $this;
    }

    /**
     * @return FlowNodeInterface
     */
    public function getTarget()
    {
        return $this->getProperty('target');
    }

    /**
     * @param FlowNodeInterface $target
     *
     * @return $this
     */
    public function setTarget(FlowNodeInterface $target)
    {
        $this->setProperty('target', $target);
        return $this;
    }

    /**
     * @return callable
     */
    public function getCondition()
    {
        return $this->getProperty('CONDITION', function () {
            return true;
        });
    }

    /**
     * @return bool
     */
    public function isDefault()
    {
        return $this->getProperty('IS_DEFAULT', false);
    }

    /**
     * @return bool
     */
    public function hasCondition()
    {
        return $this->getProperty('CONDITION', null) !== null;
    }
}