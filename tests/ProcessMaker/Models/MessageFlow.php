<?php

namespace ProcessMaker\Models;

use ProcessMaker\Nayra\Bpmn\MessageFlowTrait;
use ProcessMaker\Nayra\Contracts\Bpmn\FlowNodeInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\MessageFlowInterface;

/**
 * End event implementation.
 *
 * @package ProcessMaker\Models
 */
class MessageFlow implements MessageFlowInterface
{

    use MessageFlowTrait,
        LocalFlowNodeTrait,
        LocalProcessTrait,
        LocalPropertiesTrait;

    private $message;
    private $source;
    private $target;

    public function getMessage()
    {
        return $this->message;
    }

    public function getSource()
    {
        return $this->source;
    }

    public function getTarget()
    {
        return $this->target;
    }

    public function setSource(FlowNodeInterface $source)
    {
        $this->source = $source;
        return $this;
    }

    public function setTarget(FlowNodeInterface $target)
    {
        $this->target = $target;
        return $this;
    }
}
