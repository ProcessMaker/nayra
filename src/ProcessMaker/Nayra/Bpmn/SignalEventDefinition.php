<?php

namespace ProcessMaker\Nayra\Bpmn;

use ProcessMaker\Nayra\Contracts\Bpmn\SignalEventDefinitionInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\SignalInterface;

class SignalEventDefinition implements SignalEventDefinitionInterface
{
    /**
     * @var string $id
     */
    private $id;

    /**
     * @var string $signal
     */
    private $signal;

    /**
     * Returns the element's id
     *
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Sets the element id
     *
     * @param $value
     */
    public function setId($value)
    {
        $this->id = $value;
    }

    /**
     * Sets the signal used in the signal event definition
     *
     * @param SignalInterface $value
     */
    public function setPayload($value)
    {
        $this->signal = $value;
    }

    /**
     * Returns the signal of the signal event definition
     * @return mixed
     */
    public function getPayload()
    {
        return $this->signal;
    }
}