<?php

namespace ProcessMaker\Nayra\Bpmn;

use ProcessMaker\Nayra\Contracts\Bpmn\ConditionedTransitionInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\GatewayInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\TransitionInterface;
use ProcessMaker\Nayra\Contracts\RepositoryInterface;

/**
 * Base behavior for gateway elements.
 *
 * @package ProcessMaker\Nayra\Bpmn
 */
trait ConditionedGatewayTrait
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
     * Initialize the ConditionedGatewayTrait.
     *
     */
    protected function initConditionedGatewayTrait()
    {
        $this->conditionedTransitions = new Collection;
    }

    /**
     * Add and output conditioned transition.
     *
     * @param ConditionedTransitionInterface $transition
     * @param callable $condition
     *
     * @return ConditionedTransitionInterface
     */
    protected function conditionedTransition(
        ConditionedTransitionInterface $transition,
        callable $condition
    ) {
        $transition->setCondition($condition);
        $this->conditionedTransitions->push($transition);
        return $transition;
    }

    /**
     * Add the default output transition.
     *
     * @param TransitionInterface $transition
     *
     * @return TransitionInterface
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
     * @param RepositoryInterface $factory
     */
    public function buildFlowTransitions(RepositoryInterface $factory)
    {
        $this->setRepository($factory);
        $flows = $this->getFlows();
        $defaultFlow = $this->getProperty(GatewayInterface::BPMN_PROPERTY_DEFAULT);
        foreach ($flows as $flow) {
            $isDefault = $defaultFlow === $flow;
            if ($isDefault || $flow->hasCondition()) {
                $this->buildConditionedConnectionTo($flow->getTarget(), $flow->getCondition(), $isDefault);
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
