<?php

namespace ProcessMaker\Nayra\Bpmn;

use ProcessMaker\Nayra\Contracts\Bpmn\FlowNodeInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\GatewayInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\IntermediateThrowEventInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\StateInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\TokenInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\TransitionInterface;
use ProcessMaker\Nayra\Contracts\RepositoryInterface;

/**
 * End event behavior's implementation.
 *
 * @package ProcessMaker\Nayra\Bpmn
 */
trait IntermediateThrowEventTrait
{

    use FlowNodeTrait;

    /**
     * Receive tokens.
     *
     * @var StateInterface
     */
    private $endState;

    /**
     * Close the tokens.
     *
     * @var EndTransition
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

        $this->transition=new IntermediateThrowEventTransition($this);

        $this->transition->attachEvent(TransitionInterface::EVENT_AFTER_CONSUME, function()  {

            foreach ($this->getOwnerProcess()->getInstances()->toArray() as $instance) {
                $this->getRepository()
                    ->getTokenRepository()
                    ->persistThrowEventTokenPassed($this, $this->getTokens($instance));
            }

            $this->notifyEvent(IntermediateThrowEventInterface::EVENT_THROW_TOKEN_PASSED, $this);
        });
    }

    /**
     * Get an input to the element.
     *
     * @return StateInterface
     */
    public function getInputPlace()
    {
        $incomingPlace=new State($this, GatewayInterface::TOKEN_STATE_INCOMING);
        $incomingPlace->connectTo($this->transition);
        $incomingPlace->attachEvent(State::EVENT_TOKEN_ARRIVED, function (TokenInterface $token) {
            $collaboration = $this->getEventDefinitions()->item(0)->getPayload()->getMessageFlow()->getCollaboration();
            $collaboration->send($this->getEventDefinitions()->item(0), $token);

            foreach ($this->getOwnerProcess()->getInstances()->toArray() as $instance) {
                $this->getRepository()
                    ->getTokenRepository()
                    ->persistThrowEventTokenArrives($this, $this->getTokens($instance));
            }

            $this->notifyEvent(IntermediateThrowEventInterface::EVENT_THROW_TOKEN_ARRIVES, $this, $token);
        });

        $incomingPlace->attachEvent(State::EVENT_TOKEN_CONSUMED, function (TokenInterface $token) {

            foreach ($this->getOwnerProcess()->getInstances()->toArray() as $instance) {
                $this->getRepository()
                    ->getTokenRepository()
                    ->persistThrowEventTokenConsumed($this, $this->getTokens($instance));
            }

            $this->notifyEvent(IntermediateThrowEventInterface::EVENT_THROW_TOKEN_CONSUMED, $this, $token);
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
        $this->transition->connectTo($target->getInputPlace());
        return $this;
    }
}
