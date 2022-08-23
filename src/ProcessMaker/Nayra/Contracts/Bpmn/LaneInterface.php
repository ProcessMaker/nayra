<?php

namespace ProcessMaker\Nayra\Contracts\Bpmn;

/**
 * Lane interface.
 */
interface LaneInterface extends EntityInterface
{
    const BPMN_PROPERTY_FLOW_NODE = 'flowNode';

    const BPMN_PROPERTY_FLOW_NODE_REF = 'flowNodeRef';

    const BPMN_PROPERTY_CHILD_LANE_SET = 'childLaneSet';

    /**
     * Get the name of the lane.
     *
     * @return string
     */
    public function getName();

    /**
     * Get the flow nodes of the lane.
     *
     * @return CollectionInterface
     */
    public function getFlowNodes();

    /**
     * Get the child lanes of the lane.
     *
     * @return CollectionInterface
     */
    public function getChildLaneSets();

    /**
     * Set the name of the lane.
     *
     * @param string $name
     *
     * @return $this
     */
    public function setName($name);

    /**
     * Set the flow nodes of the lane.
     *
     * @param CollectionInterface $nodes
     *
     * @return $this
     */
    public function setFlowNodes(CollectionInterface $nodes);

    /**
     * Set the child lanes of the lane.
     *
     * @param CollectionInterface $nodes
     *
     * @return $this
     */
    public function setChildLaneSets(CollectionInterface $nodes);
}
