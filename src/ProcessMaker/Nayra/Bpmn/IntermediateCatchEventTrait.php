<?php

namespace ProcessMaker\Nayra\Bpmn;

use ProcessMaker\Nayra\Contracts\Bpmn\CollectionInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\EventDefinitionInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\EventInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\FlowInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\IntermediateCatchEventInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\StateInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\TokenInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\TransitionInterface;
use ProcessMaker\Nayra\Contracts\Engine\ExecutionInstanceInterface;
use ProcessMaker\Nayra\Contracts\RepositoryInterface;

/**
 * End event behavior's implementation.
 *
 * @package ProcessMaker\Nayra\Bpmn
 */
trait IntermediateCatchEventTrait
{
    use CatchEventTrait;

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

    private $activeState;
    private $triggerPlace;

    /**
     * Build the transitions that define the element.
     *
     * @param RepositoryInterface $factory
     */
    public function buildTransitions(RepositoryInterface $factory)
    {
        $this->setRepository($factory);
        $this->activeState = new State($this, IntermediateCatchEventInterface::TOKEN_STATE_ACTIVE);
        $this->triggerPlace = new State($this, IntermediateCatchEventInterface::TOKEN_STATE_EVENT_CATCH);
        $this->transition = new IntermediateCatchEventTransition($this);
        $this->activeState->connectTo($this->transition);
        $this->triggerPlace->connectTo($this->transition);

        $this->activeState->attachEvent(State::EVENT_TOKEN_ARRIVED, function (TokenInterface $token) {
            $this->getRepository()
                ->getTokenRepository()
                ->persistCatchEventTokenArrives($this, $token);

            $this->notifyEvent(IntermediateCatchEventInterface::EVENT_CATCH_TOKEN_ARRIVES, $this, $token);
            // If there are timer event definitions, register them to send the corresponding timer events
            $this->scheduleTimerEvents($token);
        });

        $this->activeState->attachEvent(State::EVENT_TOKEN_CONSUMED, function (TokenInterface $token) {
            $this->getRepository()
                ->getTokenRepository()
                ->persistCatchEventTokenConsumed($this, $token);

            $this->notifyEvent(IntermediateCatchEventInterface::EVENT_CATCH_TOKEN_CONSUMED, $this, $token);
        });

        $this->transition->attachEvent(TransitionInterface::EVENT_AFTER_CONSUME, function (TransitionInterface $transition, Collection $consumedTokens) {
            $this->getRepository()
                ->getTokenRepository()
                ->persistCatchEventTokenPassed($this, $consumedTokens);

            $this->notifyEvent(IntermediateCatchEventInterface::EVENT_CATCH_TOKEN_PASSED, $this);
        });

        $this->transition->attachEvent(
            TransitionInterface::EVENT_AFTER_TRANSIT,
            function (TransitionInterface $transition, CollectionInterface $consumeTokens) {
                $this->notifyEvent(EventInterface::EVENT_EVENT_TRIGGERED, $this, $transition, $consumeTokens);
            }
        );

        $this->triggerPlace->attachEvent(State::EVENT_TOKEN_ARRIVED, function (TokenInterface $token) {
            $this->getRepository()
                ->getTokenRepository()
                ->persistCatchEventMessageArrives($this, $token);
            $this->notifyEvent(IntermediateCatchEventInterface::EVENT_CATCH_MESSAGE_CATCH, $this, $token);
        });
        $this->triggerPlace->attachEvent(State::EVENT_TOKEN_CONSUMED, function (TokenInterface $token) {
            $this->getRepository()
                ->getTokenRepository()
                ->persistCatchEventMessageConsumed($this, $token);
            $this->notifyEvent(IntermediateCatchEventInterface::EVENT_CATCH_MESSAGE_CONSUMED, $this, $token);
        });
    }

    /**
     * Get an input to the element.
     *
     * @param \ProcessMaker\Nayra\Contracts\Bpmn\FlowInterface|null $targetFlow
     *
     * @return StateInterface
     */
    public function getInputPlace(FlowInterface $targetFlow = null)
    {
        $incomingPlace = new State($this);

        $transition = new Transition($this, false);
        $incomingPlace->connectTo($transition);
        $transition->connectTo($this->activeState);

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
        $this->transition->connectTo($targetFlow->getTarget()->getInputPlace($targetFlow));
        return $this;
    }

    /**
     * To implement the MessageListener interface
     *
     * @param EventDefinitionInterface $message
     * @param ExecutionInstanceInterface|null $instance
     *
     * @return $this
     */
    public function execute(EventDefinitionInterface $message, ExecutionInstanceInterface $instance = null)
    {
        if ($instance !== null && $this->getActiveState()->getTokens($instance)->count() > 0) {
            // with a new token in the trigger place, the event catch element will be fired
            $this->triggerPlace->addNewToken($instance);
        }
        return $this;
    }

    /**
     * Get the activation transition of the element
     *
     * @return TransitionInterface
     */
    public function getActivationTransition()
    {
        return $this->transition;
    }

    /**
     * Get the active state of the element
     *
     * @return StateInterface
     */
    public function getActiveState()
    {
        return $this->activeState;
    }
}
