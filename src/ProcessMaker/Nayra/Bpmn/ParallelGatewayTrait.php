<?php

namespace ProcessMaker\Nayra\Bpmn;

use ProcessMaker\Nayra\Contracts\Bpmn\FlowInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\GatewayInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\StateInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\TokenInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\TransitionInterface;
use ProcessMaker\Nayra\Contracts\RepositoryInterface;

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
     * @param RepositoryInterface $factory
     */
    public function buildTransitions(RepositoryInterface $factory)
    {
        $this->setRepository($factory);
        $this->transition = new ParallelGatewayTransition($this);
        $this->transition->attachEvent(TransitionInterface::EVENT_BEFORE_TRANSIT, function () {
            $this->notifyEvent(GatewayInterface::EVENT_GATEWAY_ACTIVATED, $this);
        });
    }

    /**
     * Get an input to the element.
     *
     * @param FlowInterface|null $targetFlow
     *
     * @return StateInterface
     */
    public function getInputPlace(FlowInterface $targetFlow = null)
    {
        $incomingPlace = new State($this, GatewayInterface::TOKEN_STATE_INCOMING);
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
     * @param \ProcessMaker\Nayra\Contracts\Bpmn\FlowInterface $targetFlow
     *
     * @return $this
     */
    protected function buildConnectionTo(FlowInterface $targetFlow)
    {
        $outgoingPlace = new State($this, GatewayInterface::TOKEN_STATE_OUTGOING);
        $outgoingTransition = new Transition($this);
        $outgoingTransition->attachEvent(
            TransitionInterface::EVENT_AFTER_CONSUME,
            function (TransitionInterface $transition, Collection $consumedTokens) {
                foreach ($consumedTokens as $token) {
                    $this->getRepository()
                        ->getTokenRepository()
                        ->persistGatewayTokenPassed($this, $token);
                }

                $this->notifyEvent(GatewayInterface::EVENT_GATEWAY_TOKEN_PASSED, $this, $transition);
            }
        );
        $this->transition->connectTo($outgoingPlace);
        $outgoingPlace->connectTo($outgoingTransition);
        $outgoingTransition->connectTo($targetFlow->getTarget()->getInputPlace($targetFlow));
        return $this;
    }
}
