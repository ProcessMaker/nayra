<?php

namespace ProcessMaker\Nayra\Bpmn;

use ProcessMaker\Nayra\Contracts\Bpmn\FlowNodeInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\GatewayInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\StateInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\TokenInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\TransitionInterface;
use ProcessMaker\Nayra\Contracts\FactoryInterface;
use ProcessMaker\Nayra\Contracts\Repositories\RepositoryFactoryInterface;

/**
 * Base implementation for a parallel gateway.
 *
 * @package ProcessMaker\Nayra\Bpmn
 */
trait ParallelGatewayTrait
{

    use FlowNodeTrait;

    /**
     * @var TransitionInterface
     */
    private $transition;

    /**
     * Build the transitions that define the element.
     *
     * @param FactoryInterface|RepositoryFactoryInterface $factory
     */
    public function buildTransitions(FactoryInterface $factory)
    {
        $this->setFactory($factory);
        $this->transition=new ParallelGatewayTransition($this);
        $this->transition->attachEvent(TransitionInterface::EVENT_BEFORE_TRANSIT, function()  {
            $this->notifyEvent(GatewayInterface::EVENT_GATEWAY_ACTIVATED, $this);
        });
    }

    /**
     * Get an input to the element.
     *
     * @return StateInterface
     */
    public function getInputPlace() {
        $incomingPlace=new State($this, GatewayInterface::TOKEN_STATE_INCOMING);
        $incomingPlace->connectTo($this->transition);
        $incomingPlace->attachEvent(State::EVENT_TOKEN_ARRIVED, function (TokenInterface $token) {
            $this->notifyEvent(GatewayInterface::EVENT_GATEWAY_TOKEN_ARRIVES, $this, $token);
        });
        $incomingPlace->attachEvent(State::EVENT_TOKEN_CONSUMED, function (TokenInterface $token) {
            $this->notifyEvent(GatewayInterface::EVENT_GATEWAY_TOKEN_CONSUMED, $this, $token);
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
        $outgoingPlace = new State($this, GatewayInterface::TOKEN_STATE_OUTGOING);
        $outgoingTransition = new Transition($this);
        $outgoingTransition->attachEvent(
            TransitionInterface::EVENT_AFTER_CONSUME,
            function(TransitionInterface $transition)  {
                $this->notifyEvent(GatewayInterface::EVENT_GATEWAY_TOKEN_PASSED, $this, $transition);
            }
        );
        $this->transition->connectTo($outgoingPlace);
        $outgoingPlace->connectTo($outgoingTransition);
        $outgoingTransition->connectTo($target->getInputPlace());
        return $this;
    }
}
