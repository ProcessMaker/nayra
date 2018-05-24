<?php

namespace ProcessMaker\Nayra\Bpmn;

use ProcessMaker\Nayra\Bpmn\Collection;
use ProcessMaker\Nayra\Contracts\Bpmn\CollectionInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\LaneSetInterface;

/**
 * LaneSet class
 *
 */
class LaneSet implements LaneSetInterface
{
    use BaseTrait;

    /**
     * Initialize the lane set.
     *
     */
    protected function initLaneSet()
    {
        $this->setLanes(new Collection);
    }

    /**
     * Get the name of the lane set.
     *
     * @return string
     */
    public function getName()
    {
        return $this->getProperty(self::BPMN_PROPERTY_NAME);
    }

    /**
     * Get the lanes of the lane set.
     *
     * @return \ProcessMaker\Nayra\Contracts\Bpmn\CollectionInterface
     */
    public function getLanes()
    {
        return $this->getProperty(self::BPMN_PROPERTY_LANE);
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
     * Set the lanes of the lane set.
     *
     * @param \ProcessMaker\Nayra\Contracts\Bpmn\CollectionInterface $lanes
     *
     * @return $this
     */
    public function setLanes(CollectionInterface $lanes)
    {
        return $this->setProperty(self::BPMN_PROPERTY_LANE, $lanes);
    }
}
