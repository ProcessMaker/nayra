<?php

namespace ProcessMaker\Nayra\Contracts\Bpmn;

/**
 * EventDefinition interface.
 *
 * @package ProcessMaker\Nayra\Contracts\Bpmn
 */
interface EventDefinitionInterface
{
    /**
     * Returns the element's id
     *
     * @return mixed
     */
    public function getId();

    /**
     * Sets the element id
     *
     * @param $value
     * @return mixed
     */
    public function setId($value);
}
