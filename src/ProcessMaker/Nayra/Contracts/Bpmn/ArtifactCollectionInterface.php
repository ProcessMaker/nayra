<?php

namespace ProcessMaker\Nayra\Contracts\Bpmn;

/**
 * Collection of artifacts.
 *
 * @package ProcessMaker\Nayra\Contracts\Bpmn
 */
interface ArtifactCollectionInterface extends CollectionInterface
{

    /**
     * Add an element to the collection.
     *
     * @param ArtifactInterface $element
     *
     * @return $this
     */
    public function add(ArtifactInterface $element);
}
