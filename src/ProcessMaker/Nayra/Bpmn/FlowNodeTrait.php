<?php

namespace ProcessMaker\Nayra\Bpmn;

use ProcessMaker\Nayra\Contracts\Bpmn\FlowInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\FlowNodeInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\GatewayInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\ProcessInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\StateInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\TokenInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\TransitionInterface;
use ProcessMaker\Nayra\Contracts\Engine\ExecutionInstanceInterface;
use ProcessMaker\Nayra\Contracts\RepositoryInterface;

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

    /**
     * States classified by name
     *
     * @var array
     */
    private $statesByName = [];

    /**
     *
     * @var Process
     */
    private $process;

    /**
     * Initialize flow node.
     *
     */
    protected function initFlowNode()
    {
        $this->setProperty(FlowNodeInterface::BPMN_PROPERTY_OUTGOING, new Collection);
        $this->setProperty(FlowNodeInterface::BPMN_PROPERTY_INCOMING, new Collection);
    }

    /**
     * Get tokens in the element.
     *
     * @param \ProcessMaker\Nayra\Contracts\Engine\ExecutionInstanceInterface $instance
     *
     * @return \ProcessMaker\Nayra\Bpmn\Collection
     */
    public function getTokens(ExecutionInstanceInterface $instance)
    {
        $tokens = [];
        foreach ($this->getStates() as $state) {
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
     * @param FlowInterface $targetFlow
     *
     * @return $this
     */
    abstract protected function buildConnectionTo(FlowInterface $targetFlow);

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
     * Used by EngineInterface implementation to build the transitions
     * related to connect nodes.
     *
     * @param \ProcessMaker\Nayra\Contracts\RepositoryInterface $factory
     */
    public function buildFlowTransitions(RepositoryInterface $factory)
    {
        $this->setRepository($factory);
        $flows = $this->getFlows();
        foreach ($flows as $flow) {
            $this->buildConnectionTo($flow);
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
        $this->statesByName[$state->getName()][] = $state;
        $state->setIndex(count($this->statesByName[$state->getName()]) - 1);
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

    /**
     * Get state by name and index.
     *
     * @param string $state
     * @param string $index
     *
     * @return StateInterface
     */
    private function getStateByName($state, $index)
    {
        return $this->statesByName[$state][(int) $index];
    }

    /**
     * Load tokens from array.
     *
     * @param ExecutionInstanceInterface $instance
     * @param TokenInterface $token
     *
     * @return $this
     */
    public function addToken(ExecutionInstanceInterface $instance, TokenInterface $token)
    {
        $state = $this->getStateByName($token->getStatus(), $token->getIndex());
        $state->addToken($instance, $token, true);
        return $this;
    }

    /**
     * Create a flow to a target node.
     *
     * @param \ProcessMaker\Nayra\Contracts\Bpmn\FlowNodeInterface $target
     * @param RepositoryInterface $factory
     * @param array $properties
     * @return $this
     * @internal param FlowRepositoryInterface $flowRepository
     */
    public function createFlowTo(FlowNodeInterface $target, RepositoryInterface $factory, $properties = [])
    {
        $flow = $factory->createFlow();
        $flow->setSource($this);
        $flow->setTarget($target);
        $flow->setProperties($properties);
        $this->addProperty(FlowNodeInterface::BPMN_PROPERTY_OUTGOING, $flow);
        $target->addProperty(FlowNodeInterface::BPMN_PROPERTY_INCOMING, $flow);
        if (!empty($properties[FlowInterface::BPMN_PROPERTY_IS_DEFAULT])) {
            $this->setProperty(GatewayInterface::BPMN_PROPERTY_DEFAULT, $flow);
        }
        return $this;
    }

    /**
     * Get the incoming flows.
     *
     * @return FlowCollectionInterface
     * @codeCoverageIgnore
     */
    public function getIncomingFlows()
    {
        return $this->getProperty(FlowNodeInterface::BPMN_PROPERTY_INCOMING);
    }

    /**
     * Get the outgoing flows.
     *
     * @return FlowCollectionInterface
     */
    public function getOutgoingFlows()
    {
        return $this->getProperty(FlowNodeInterface::BPMN_PROPERTY_OUTGOING);
    }

    /**
     * Get Process of the node.
     *
     * @return ProcessInterface
     * @codeCoverageIgnore
     */
    public function getProcess()
    {
        return $this->process;
    }

    /**
     * Get Process of the node.
     *
     * @param ProcessInterface $process
     *
     * @return ProcessInterface
     * @codeCoverageIgnore
     */
    public function setProcess(ProcessInterface $process)
    {
        $this->process = $process;
        return $this;
    }

    /**
     * Get the outgoing flows.
     *
     * @return FlowCollectionInterface
     */
    public function getFlows()
    {
        return $this->getProperty(FlowNodeInterface::BPMN_PROPERTY_OUTGOING);
    }
}
