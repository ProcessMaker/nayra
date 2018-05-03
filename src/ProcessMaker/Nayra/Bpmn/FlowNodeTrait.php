<?php

namespace ProcessMaker\Nayra\Bpmn;

use ProcessMaker\Nayra\Bpmn\Collection;
use ProcessMaker\Nayra\Contracts\Bpmn\CollectionInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\FlowNodeInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\StateInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\TransitionInterface;
use ProcessMaker\Nayra\Contracts\Engine\ExecutionInstanceInterface;
use ProcessMaker\Nayra\Contracts\Repositories\RepositoryFactoryInterface;

/**
 * Flow node define the behavior of a element that can be used as
 * a source or target element in a flow.
 *
 * @package ProcessMaker\Nayra\Bpmn
 */
trait FlowNodeTrait
{
    use  FlowElementTrait, BpmnEventsTrait;

    /**
     * States used as input for a target node.
     *
     * @var StateInterface[]
     */
    private $inputs = [];

    /**
     * Transitions that define the behavior of the node.
     *
     * @var TransitionInterface[]
     */
    private $transitions = [];

    /**
     * States that define the behavior of the node.
     *
     * @var StateInterface[]
     */
    private $states = [];

    protected function initFlowNode()
    {
        $this->setProperty(FlowNodeInterface::BPMN_PROPERTY_OUTGOING, new Collection);
        $this->setProperty(FlowNodeInterface::BPMN_PROPERTY_INCOMING, new Collection);
    }

    /**
     * Get tokens in the element.
     *
     * @param \ProcessMaker\Nayra\Contracts\Bpmn\DataStoreInterface $dataStore
     *
     * @return CollectionInterface
     */
    public function getTokens(ExecutionInstanceInterface $instance)
    {
        $tokens = [];
        foreach($this->getStates() as $state) {
            $tokens = array_merge($tokens, $state->getTokens($instance)->toArray());
        }
        return new Collection($tokens);
    }

    /**
     * Add input point to the node.
     *
     * @param StateInterface $input
     *
     * @return $this
     */
    protected function addInput(StateInterface $input)
    {
        $this->inputs[] = $input;
        return $this;
    }

    /**
     * Register a transition for the element.
     *
     * @param TransitionInterface $transition
     *
     * @return $this
     */
    public function addTransition(TransitionInterface $transition)
    {
        $this->transitions[] = $transition;
        return $this;
    }

    /**
     * Concrete classes like Activities, Gateways, should implement a method
     * that build the connections to other nodes.
     *
     * @param FlowNodeInterface $target
     *
     * @return $this
     */
    abstract protected function buildConnectionTo(FlowNodeInterface $target);

    /**
     * Get the transition objects of the node.
     *
     * @return TransitionInterface[]
     */
    public function getTransitions()
    {
        return $this->transitions;
    }

    /**
     * Used by EngineInterface implementatión to build the transitions
     * related to connect nodes.
     *
     * @param RepositoryFactoryInterface $factory
     */
    public function buildFlowTransitions(RepositoryFactoryInterface $factory)
    {
        $this->setFactory($factory);
        $flows = $this->getFlows();
        foreach($flows as $flow) {
            $this->buildConnectionTo($flow->getTarget());
        }
    }

    /**
     * Add a state for the node element.
     *
     * @param StateInterface $state
     *
     * @return $this
     */
    public function addState(StateInterface $state)
    {
        $this->states[] = $state;
        return $this;
    }

    /**
     * Get the states of the node element.
     *
     * @return StateInterface[]
     */
    public function getStates()
    {
        return $this->states;
    }
}
