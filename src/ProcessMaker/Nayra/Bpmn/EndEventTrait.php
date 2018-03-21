<?php

namespace ProcessMaker\Nayra\Bpmn;

use ProcessMaker\Nayra\Bpmn\EntityTrait;
use ProcessMaker\Nayra\Bpmn\EndTransition;
use ProcessMaker\Nayra\Contracts\Bpmn\EventNodeInterface;
use ProcessMaker\Nayra\Bpmn\BpmnEventsTrait;
use ProcessMaker\Nayra\Bpmn\State;
use ProcessMaker\Nayra\Contracts\Bpmn\StateInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\TransitionInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\FlowNodeInterface;
use ProcessMaker\Nayra\Contracts\Repositories\RepositoryFactoryInterface;

/**
 * End event behavior's implementation.
 *
 * @package ProcessMaker\Nayra\Bpmn
 */
trait EndEventTrait
{

    use FlowNodeTrait;

    /**
     * Recieve tokens.
     *
     * @var StateInterface
     */
    private $endState;

    /**
     * Close the tokens.
     *
     * @var EndTransition
     */
    private $transition;

    /**
     * Build the transitions that define the element.
     *
     * @param RepositoryFactoryInterface $factory
     */
    public function buildTransitions(RepositoryFactoryInterface $factory)
    {
        $this->setFactory($factory);
        $this->endState = new State($this);
        $this->transition = new EndTransition($this);
        $this->endState->connectTo($this->transition);
        $this->transition->attachEvent(
            TransitionInterface::EVENT_AFTER_TRANSIT,
            function () {
                $this->fireEvent(EventNodeInterface::EVENT_EVENT_TRIGGERED);
            }
        );
    }

    /**
     * Get an input to the element.
     *
     * @return StateInterface
     */
    public function getInputPlace()
    {
        return $this->endState;
    }

    /**
     * Create a connection to a target node.
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
}
