<?php

namespace ProcessMaker\Nayra\Contracts\Bpmn;

/**
 * Lane set interface.
 */
interface LaneSetInterface extends EntityInterface
{
    const BPMN_PROPERTY_LANE = 'lane';

    /**
     * Get the name of the lane set.
     *
     * @return string
     */
    public function getName();

    /**
     * Get the lanes of the lane set.
     *
     * @return CollectionInterface
     */
    public function getLanes();

    /**
     * Set the name of the lane set.
     *
     * @param string $name
     *
     * @return $this
     */
    public function setName($name);

    /**
     * Set the lanes of the lane set.
     *
     * @param CollectionInterface $lanes
     *
     * @return $this
     */
    public function setLanes(CollectionInterface $lanes);
}
