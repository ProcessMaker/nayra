<?php

namespace ProcessMaker\Nayra\Contracts\Bpmn;


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
    /**
     * Create a flow to a target node.
     *
     * @param \ProcessMaker\Nayra\Contracts\Bpmn\FlowNodeInterface $target
     *
     * @return $this
     */
    public function createFlowTo(FlowNodeInterface $target, FlowRepositoryInterface $flowRepository, $properties=[]);

    /**
     * @return FlowCollectionInterface
     */
    public function getFlows();

    /**
     * @param TransitionInterface $transition
     *
     * @return $this
     */
    public function addTransition(TransitionInterface $transition);

    /**
     *
     * @return TransitionInterface[]
     */
    public function getTransitions();

    /**
     * @param RepositoryFactoryInterface $factory Factory that will be used to create tokens.
     *
     * @return $this
     */
    public function buildTransitions(RepositoryFactoryInterface $factory);

    /**
     * @param RepositoryFactoryInterface $factory Factory that will be used to create tokens.
     *
     * @return $this
     */
    public function buildFlowTransitions(RepositoryFactoryInterface $factory);

}
