<?php

namespace ProcessMaker\Nayra\Bpmn;

use ProcessMaker\Nayra\Contracts\Bpmn\EventInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\FlowNodeInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\TransitionInterface;
use ProcessMaker\Nayra\Contracts\Repositories\RepositoryFactoryInterface;

/**
 * Implementation of the behavior of a start event.
 *
 * @package ProcessMaker\Nayra\Bpmn
 */
trait StartEventTrait
{

    use FlowNodeTrait;
    /**
     *
     * @var StartTransition
     */
    private $transition;

    public function buildTransitions(RepositoryFactoryInterface $factory)
    {
        $this->setFactory($factory);
        $this->transition = new StartTransition($this);
        $this->transition->attachEvent(
            TransitionInterface::EVENT_BEFORE_TRANSIT,
            function() {
                $this->notifyEvent(EventInterface::EVENT_EVENT_TRIGGERED, $this);
            }
        );
    }

    public function getInputPlace()
    {
        return null;
    }

    /**
     * Create a flow to a target node.
     *
     * @param \ProcessMaker\Nayra\Contracts\Bpmn\FlowNodeInterface $target
     *
     * @return $this
     */
    protected function buildConnectionTo(FlowNodeInterface $target)
    {
        $this->transition->connectTo($target->getInputPlace());
        return $this;
    }

    public function start()
    {
        $this->transition->start();
    }
}
