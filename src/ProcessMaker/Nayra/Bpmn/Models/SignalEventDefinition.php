<?php

namespace ProcessMaker\Nayra\Bpmn\Models;

use ProcessMaker\Nayra\Bpmn\BaseTrait;
use ProcessMaker\Nayra\Contracts\Bpmn\EventDefinitionInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\FlowNodeInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\SignalEventDefinitionInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\SignalInterface;
use ProcessMaker\Nayra\Contracts\Engine\ExecutionInstanceInterface;

class SignalEventDefinition implements SignalEventDefinitionInterface
{

    use BaseTrait;
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

    /**
     * Assert the event definition rule for trigger the event.
     *
     * @param EventDefinitionInterface $event
     * @param FlowNodeInterface $target
     * @param ExecutionInstanceInterface $instance
     *
     * @return boolean
     */
    public function assertsRule(EventDefinitionInterface $event, FlowNodeInterface $target, ExecutionInstanceInterface $instance = null)
    {
        return true;
    }
}
