<?php

namespace ProcessMaker\Nayra\Bpmn;

/**
 * Path of flows and elements.
 */
class Path
{
    protected $elements;

    /**
     * Path constructor.
     *
     * @param array $elements
     */
    public function __construct(array $elements)
    {
        $this->elements = $elements;
    }

    /**
     * Get the elements of the path.
     *
     * @return array
     */
    public function getElements()
    {
        return $this->elements;
    }
}
