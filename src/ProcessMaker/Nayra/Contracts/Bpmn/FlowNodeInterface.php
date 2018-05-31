<?php

namespace ProcessMaker\Nayra\Contracts\Bpmn;

use ProcessMaker\Nayra\Contracts\Engine\ExecutionInstanceInterface;
use ProcessMaker\Nayra\Contracts\FactoryInterface;
use ProcessMaker\Nayra\Contracts\Repositories\FlowRepositoryInterface;
use ProcessMaker\Nayra\Contracts\Repositories\RepositoryFactoryInterface;

/**
 * Flow node define the behavior of a element that can be used as
 * a source or target element in a flow.
 *
 * @package ProcessMaker\Nayra\Contracts\Bpmn
 */
interface FlowNodeInterface extends FlowElementInterface
{
    const BPMN_PROPERTY_INCOMING = 'incoming';
    const BPMN_PROPERTY_OUTGOING = 'outgoing';

    /**
     * Create a flow to a target node.
     *
     * @param \ProcessMaker\Nayra\Contracts\Bpmn\FlowNodeInterface $target
     * @param FactoryInterface $factory
     * @param array $properties
     * @return $this
     * @internal param FlowRepositoryInterface $flowRepository
     */
    public function createFlowTo(FlowNodeInterface $target, FactoryInterface $factory, $properties=[]);

    /**
     * Get the outgoing flows.
     *
     * @return FlowCollectionInterface
     */
    public function getFlows();

    /**
     * Get the incoming flows.
     *
     * @return FlowCollectionInterface
     */
    public function getIncomingFlows();

    /**
     * Add a transition rule for the node element.
     *
     * @param TransitionInterface $transition
     *
     * @return $this
     */
    public function addTransition(TransitionInterface $transition);

    /**
     * Get the transitions rules of the node element.
     *
     * @return TransitionInterface[]
     */
    public function getTransitions();

    /**
     * Build the transition rules of the node element.
     *
     * @param RepositoryFactoryInterface $factory Factory that will be used to create tokens.
     *
     * @return $this
     */
    public function buildTransitions(RepositoryFactoryInterface $factory);

    /**
     * Build the transition rules of the outgoing flows.
     *
     * @param RepositoryFactoryInterface $factory Factory that will be used to create tokens.
     *
     * @return $this
     */
    public function buildFlowTransitions(RepositoryFactoryInterface $factory);

    /**
     * Add a state for the node element.
     *
     * @param StateInterface $state
     *
     * @return $this
     */
    public function addState(StateInterface $state);

    /**
     * Get the states of the node element.
     *
     * @return StateInterface[]
     */
    public function getStates();

    /**
     * Get tokens in the activity.
     *
     * @param \ProcessMaker\Nayra\Contracts\Engine\ExecutionInstanceInterface $instance
     *
     * @return CollectionInterface
     */
    public function getTokens(ExecutionInstanceInterface $instance);

    /**
     * Load tokens from array.
     *
     * @param ExecutionInstanceInterface $instance
     * @param TokenInterface $token
     *
     * @return $this
     */
    public function addToken(ExecutionInstanceInterface $instance, TokenInterface $token);
}
