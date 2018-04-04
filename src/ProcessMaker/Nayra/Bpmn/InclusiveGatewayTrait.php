<?php

namespace ProcessMaker\Nayra\Bpmn;

use ProcessMaker\Nayra\Contracts\Bpmn\ActivityInterface;
use ProcessMaker\Nayra\Bpmn\ActivityTransition;
use ProcessMaker\Nayra\Contracts\Bpmn\DataStoreInterface;
use ProcessMaker\Nayra\Bpmn\EntityTrait;
use ProcessMaker\Nayra\Contracts\Bpmn\CollectionInterface;
use ProcessMaker\Nayra\Bpmn\BpmnEventsTrait;
use ProcessMaker\Nayra\Bpmn\State;
use ProcessMaker\Nayra\Contracts\Bpmn\StateInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\TokenInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\TransitionInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\GatewayInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\FlowNodeInterface;
use ProcessMaker\Nayra\Contracts\Repositories\RepositoryFactoryInterface;

/**
 * Base implementation for a inclusive gateway.
 *
 * @package ProcessMaker\Nayra\Bpmn
 */
trait InclusiveGatewayTrait
{

    use GatewayTrait;

    /**
     * @var TransitionInterface
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
        $this->transition=new InclusiveGatewayTransition($this);
        $this->transition->attachEvent(TransitionInterface::EVENT_BEFORE_TRANSIT, function()  {
            $this->fireEvent(GatewayInterface::EVENT_GATEWAY_ACTIVATED, $this);
        });
    }

    /**
     * Get an input to the element.
     *
     * @return StateInterface
     */
    public function getInputPlace() {
        $incomingPlace=new State($this);
        $incomingPlace->connectTo($this->transition);
        $incomingPlace->attachEvent(State::EVENT_TOKEN_ARRIVED, function (TokenInterface $token) {
            $this->fireEvent(GatewayInterface::EVENT_GATEWAY_TOKEN_ARRIVES, $this, $token);
        });
        $incomingPlace->attachEvent(State::EVENT_TOKEN_CONSUMED, function (TokenInterface $token) {
            $this->fireEvent(GatewayInterface::EVENT_GATEWAY_TOKEN_CONSUMED, $this, $token);
        });
        return $incomingPlace;
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
        return $this->buildConditionedConnectionTo($target, function () {
            return true;
        }, false);
    }

    /**
     * Add a conditioned transition for the inclusive gateway.
     *
     * @param FlowNodeInterface $target
     * @param callable $condition
     * @param bool $default
     *
     * @return $this
     */
    protected function buildConditionedConnectionTo(FlowNodeInterface $target, callable $condition, $default=false) {
        $outgoingPlace = new State($this);
        if ($default) {
            $outgoingTransition = $this->setDefaultTransition(new DefaultTransition($this));
        } else {
            $outgoingTransition = $this->conditionedTransition(
                new ConditionedTransition($this),
                $condition
            );
        }
        $outgoingTransition->attachEvent(TransitionInterface::EVENT_AFTER_TRANSIT, function(TransitionInterface $transition)  {
            $this->fireEvent(GatewayInterface::EVENT_GATEWAY_TOKEN_PASSED, $this);
        });
        $this->transition->connectTo($outgoingPlace);
        $outgoingPlace->connectTo($outgoingTransition);
        $outgoingTransition->connectTo($target->getInputPlace());
        return $this;
    }
}
