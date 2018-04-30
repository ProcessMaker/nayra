<?php

namespace ProcessMaker\Nayra\Bpmn;

use ProcessMaker\Nayra\Contracts\Bpmn\EventDefinitionInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\TokenInterface;

/**
 * Implementation of the behavior of a start event.
 *
 * @package ProcessMaker\Nayra\Bpmn
 */
trait CatchEventTrait
{
    use FlowNodeTrait;
    /**
     * @var \ProcessMaker\Nayra\Contracts\Bpmn\EventDefinitionInterface[]
     */
    private $eventDefinitions;

    protected function initCatchEventTrait()
    {
        $this->eventDefinitions= new Collection;
    }

    public function getEventDefinitions()
    {
        return $this->eventDefinitions;
    }

    /**
     * @param EventDefinitionInterface $message
     * @param TokenInterface $token
     * @return \ProcessMaker\Nayra\Contracts\Engine\ExecutionInstanceInterface[]
     */
    public function getTargetInstances(EventDefinitionInterface $message, TokenInterface $token)
    {
        return $this->getOwnerProcess()->getInstances();
    }
}