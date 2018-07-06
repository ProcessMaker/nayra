<?php

namespace ProcessMaker\Nayra\Bpmn;

use ProcessMaker\Nayra\Contracts\Bpmn\FlowNodeInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\GatewayInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\StateInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\TokenInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\TransitionInterface;
use ProcessMaker\Nayra\Contracts\RepositoryInterface;

/**
 * Base implementation for a exclusive gateway.
 *
 * @package ProcessMaker\Nayra\Bpmn
 */
trait ExclusiveGatewayTrait
{

    use ConditionedGatewayTrait;

    /**
     * @var TransitionInterface
     */
    private $transition;

    /**
     * Build the transitions that define the element.
     *
     * @param RepositoryInterface $factory
     */
    public function buildTransitions(RepositoryInterface $factory)
    {
        $this->setRepository($factory);
        $this->transition=new ExclusiveGatewayTransition($this);
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

            $this->getRepository()
                ->getTokenRepository()
                ->persistGatewayTokenArrives($this, $token);

            $this->notifyEvent(GatewayInterface::EVENT_GATEWAY_TOKEN_ARRIVES, $this, $token);
        });
        $incomingPlace->attachEvent(State::EVENT_TOKEN_CONSUMED, function (TokenInterface $token) {

            $this->getRepository()
                ->getTokenRepository()
                ->persistGatewayTokenConsumed($this, $token);

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
        return $this->buildConditionedConnectionTo($target, function () {
            return true;
        }, false);
    }

    /**
     * Add a conditioned transition for the exclusive gateway.
     *
     * @param FlowNodeInterface $target
     * @param callable $condition
     * @param bool $default
     *
     * @return $this
     */
    protected function buildConditionedConnectionTo(FlowNodeInterface $target, callable $condition, $default=false) {
        $outgoingPlace = new State($this, GatewayInterface::TOKEN_STATE_OUTGOING);
        if ($default) {
            $outgoingTransition = $this->setDefaultTransition(new DefaultTransition($this));
        } else {
            $outgoingTransition = $this->conditionedTransition(
                new ConditionedExclusiveTransition($this),
                $condition
            );
        }
        $outgoingTransition->attachEvent(TransitionInterface::EVENT_AFTER_CONSUME, function(TransitionInterface $transition, Collection $consumedTokens)  {
            foreach ($consumedTokens as $token) {
                $this->getRepository()
                    ->getTokenRepository()
                    ->persistGatewayTokenPassed($this, $token);
            }

            $this->notifyEvent(GatewayInterface::EVENT_GATEWAY_TOKEN_PASSED, $this);
        });
        $this->transition->connectTo($outgoingPlace);
        $outgoingPlace->connectTo($outgoingTransition);
        $outgoingTransition->connectTo($target->getInputPlace());
        return $this;
    }
}
