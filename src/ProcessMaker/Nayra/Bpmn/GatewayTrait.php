<?php

namespace ProcessMaker\Nayra\Bpmn;


use ProcessMaker\Nayra\Contracts\Bpmn\ConditionedTransitionInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\TransitionInterface;
use ProcessMaker\Nayra\Contracts\Repositories\RepositoryFactoryInterface;

/**
 * Base behavior for gateway elements.
 *
 * @package ProcessMaker\Nayra\Bpmn
 */
trait GatewayTrait
{
    use FlowNodeTrait;

    /**
     * Transitions that verify the conditions of the gateway.
     *
     * @var Collection
     */
    private $conditionedTransitions;

    /**
     * Default transition associated to the gateway.
     *
     * @var TransitionInterface
     */
    private $defaultTransition;

    /**
     * Concrete gateway class should implement the logic of the
     * connection to other nodes.
     *
     * @param FlowNodeInterface $target
     * @param callable $condition
     * @param bool $default
     *
     * @return $this
     */
    abstract protected function buildConditionedConnectionTo(
        FlowNodeInterface $target,
        callable $condition,
        $default = false
    );

    /**
     * Initialize the GatewayTrait.
     *
     */
    protected function initGatewayTrait()
    {
        $this->conditionedTransitions = new Collection;
    }

    /**
     * Add and output conditioned transition.
     *
     * @param ConditionedTransitionInterface $transition
     * @param $condition
     *
     * @return ConditionedTransitionInterface
     */
    protected function conditionedTransition(
        ConditionedTransitionInterface $transition,
        $condition
    ) {
        $transition->setCondition($condition);
        $this->conditionedTransitions->push($transition);
        return $transition;
    }

    /**
     * Add the default output transition.
     *
     * @param DefaultTransitionInterface $transition
     *
     * @return DefaultTransitionInterface
     */
    protected function setDefaultTransition(TransitionInterface $transition)
    {
        $this->defaultTransition = $transition;
        return $transition;
    }

    /**
     * Overrides the build of flow transitions, to accept gateway
     * conditioned transitions.
     *
     * @param RepositoryFactoryInterface $factory
     */
    public function buildFlowTransitions(RepositoryFactoryInterface $factory)
    {
        $this->setFactory($factory);
        $flows = $this->getFlows();
        foreach ($flows as $flow) {
            if ($flow->hasCondition()) {
                $this->buildConditionedConnectionTo($flow->getTarget(), $flow->getCondition(), $flow->isDefault());
            } else {
                $this->buildConnectionTo($flow->getTarget());
            }
        }
    }

    /**
     * Returns the list of conditioned transitions of the gateway
     *
     * @return Collection
     */
    public function getConditionedTransitions()
    {
        return $this->conditionedTransitions;
    }

}
