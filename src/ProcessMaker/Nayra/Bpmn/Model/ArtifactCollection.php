<?php

namespace ProcessMaker\Nayra\Bpmn\Model;


use ProcessMaker\Nayra\Bpmn\Collection;
use ProcessMaker\Nayra\Contracts\Bpmn\ArtifactCollectionInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\ArtifactInterface;

class ArtifactCollection extends Collection implements ArtifactCollectionInterface
{

    /**
     * Add an element to the collection.
     *
     * @param ArtifactInterface $element
     *
     * @return $this
     */
    public function add(ArtifactInterface $element)
    {
        $this->push($element);
        return $this;
    }
}
