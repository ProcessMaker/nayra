<?php

namespace ProcessMaker\Models;

use ProcessMaker\Nayra\Contracts\Bpmn\FlowInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\FlowNodeInterface;
use ProcessMaker\Nayra\Bpmn\FlowTrait;

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
     * @var FlowNodeInterface
     */
    private $source;

    /**
     * @var FlowNodeInterface
     */
    private $target;

    /**
     * @return FlowNodeInterface
     */
    public function getSource()
    {
        return $this->source;
    }

    /**
     * @param FlowNodeInterface $source
     *
     * @return $this
     */
    public function setSource(FlowNodeInterface $source)
    {
        $this->source = $source;
        return $this;
    }

    /**
     * @return FlowNodeInterface
     */
    public function getTarget()
    {
        return $this->target;
    }

    /**
     * @param FlowNodeInterface $target
     *
     * @return $this
     */
    public function setTarget(FlowNodeInterface $target)
    {
        $this->target = $target;
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