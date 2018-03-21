<?php

namespace ProcessMaker\Nayra\Contracts\Bpmn;

/**
 * Collection of activities.
 *
 * @package ProcessMaker\Nayra\Contracts\Bpmn
 */
interface ActivityCollectionInterface extends CollectionInterface
{

    /**
     * Add an element to the collection.
     *
     * @param ActivityInterface $element
     *
     * @return $this
     */
    public function add(ActivityInterface $element);

    /**
     * Get an activity by index.
     *
     * @param $index
     *
     * @return ActivityInterface
     */
    public function item($index);
}
