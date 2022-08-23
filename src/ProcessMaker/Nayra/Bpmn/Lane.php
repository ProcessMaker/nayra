<?php

namespace ProcessMaker\Nayra\Bpmn;

use ProcessMaker\Nayra\Bpmn\Collection;
use ProcessMaker\Nayra\Contracts\Bpmn\CollectionInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\LaneInterface;

/**
 * Lane class
 */
class Lane implements LaneInterface
{
    use BaseTrait;

    /**
     * Initialize the lane set.
     */
    protected function initLane()
    {
        $this->setFlowNodes(new Collection);
        $this->setChildLaneSets(new Collection);
    }

    /**
     * Get the name of the lane.
     *
     * @return string
     */
    public function getName()
    {
        return $this->getProperty(self::BPMN_PROPERTY_NAME);
    }

    /**
     * Get the flow nodes of the lane.
     *
     * @return CollectionInterface
     */
    public function getFlowNodes()
    {
        return $this->getProperty(self::BPMN_PROPERTY_FLOW_NODE);
    }

    /**
     * Get the child lanes of the lane.
     *
     * @return CollectionInterface
     */
    public function getChildLaneSets()
    {
        return $this->getProperty(self::BPMN_PROPERTY_CHILD_LANE_SET);
    }

    /**
     * Set the name of the lane set.
     *
     * @param string $name
     *
     * @return $this
     */
    public function setName($name)
    {
        return $this->setProperty(self::BPMN_PROPERTY_NAME, $name);
    }

    /**
     * Set the flow nodes of the lane.
     *
     * @param CollectionInterface $nodes
     *
     * @return $this
     */
    public function setFlowNodes(CollectionInterface $nodes)
    {
        return $this->setProperty(self::BPMN_PROPERTY_FLOW_NODE, $nodes);
    }

    /**
     * Set the child lanes of the lane.
     *
     * @param \ProcessMaker\Nayra\Contracts\Bpmn\CollectionInterface $lanes
     *
     * @return $this
     */
    public function setChildLaneSets(CollectionInterface $lanes)
    {
        return $this->setProperty(self::BPMN_PROPERTY_CHILD_LANE_SET, $lanes);
    }
}
