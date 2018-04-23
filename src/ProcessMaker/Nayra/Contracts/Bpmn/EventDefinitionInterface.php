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
     */
    public function setId($value);

    /**
     * Returns the event definition payload (message, signal, etc.)
     *
     * @return mixed
     */
    public function getPayload();

    /**
     * Sets the payload (message, signal, etc.)
     * @param mixed $value
     *
     */
    public function setPayload($value);
}
