<?php

namespace ProcessMaker\Nayra\Contracts\Bpmn;

/**
 * Collection of data stores.
 *
 * @package ProcessMaker\Nayra\Contracts\Bpmn
 */
interface DataStoreCollectionInterface extends CollectionInterface
{

    /**
     * Add an element to the collection.
     *
     * @param DataStoreInterface $element
     *
     * @return $this
     */
    public function add(DataStoreInterface $element);
}
