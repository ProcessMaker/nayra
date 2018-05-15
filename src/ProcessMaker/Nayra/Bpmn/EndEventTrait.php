<?php

namespace ProcessMaker\Nayra\Bpmn;

use ProcessMaker\Nayra\Contracts\Bpmn\CollectionInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\EventInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\FlowNodeInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\IntermediateThrowEventInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\StateInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\TokenInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\TransitionInterface;
use ProcessMaker\Nayra\Contracts\Repositories\RepositoryFactoryInterface;
use ProcessMaker\Nayra\Exceptions\InvalidSequenceFlowException;

/**
 * End event behavior's implementation.
 *
 * @package ProcessMaker\Nayra\Bpmn
 */
trait EndEventTrait
{
    use ThrowEventTrait;

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
     * @param RepositoryFactoryInterface $factory
     */
    public function buildTransitions(RepositoryFactoryInterface $factory)
    {
        $this->setFactory($factory);
        $this->endState = new State($this, EventInterface::TOKEN_STATE_ACTIVE);
        $terminate = $this->findTerminateEventDefinition();
        if ($terminate) {
            $this->transition = new TerminateTransition($this);
            $this->transition->setTerminateEventDefinition($terminate);
        } else {
            $this->transition = new EndTransition($this);
        }
        $this->endState->connectTo($this->transition);
        $this->transition->attachEvent(
            TransitionInterface::EVENT_AFTER_TRANSIT,
            function (TransitionInterface $transition, CollectionInterface $consumeTokens) {
                $this->notifyEvent(EventInterface::EVENT_EVENT_TRIGGERED, $this, $transition, $consumeTokens);
            }
        );
    }

    /**
     * Get an input to the element.
     *
     * @return StateInterface
     */
    public function getInputPlace()
    {
        $this->addInput($this->endState);

        //if the element has event definition and those event definition have a payload we notify them
        //of the triggered event
        if ($this->getEventDefinitions()->count() > 0
            && method_exists($this->getEventDefinitions()->item(0), 'getPayload')) {
            $this->endState->attachEvent(State::EVENT_TOKEN_ARRIVED, function (TokenInterface $token) {
                $collaboration = $this->getEventDefinitions()->item(0)->getPayload()->getMessageFlow()->getCollaboration();
                $collaboration->send($this->getEventDefinitions()->item(0), $token);
                $this->notifyEvent(IntermediateThrowEventInterface::EVENT_THROW_TOKEN_ARRIVES, $this, $token);
            });

            $this->endState->attachEvent(State::EVENT_TOKEN_CONSUMED, function (TokenInterface $token) {
                $this->notifyEvent(IntermediateThrowEventInterface::EVENT_THROW_TOKEN_CONSUMED, $this, $token);
            });
        }

        return $this->endState;
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
        throw new InvalidSequenceFlowException('An end event cannot have outgoing flows.');
    }

    /**
     * Find a TerminateEventDefinition whit in the event definitions
     *
     * @return TerminateEventDefinition|null
     */
    private function findTerminateEventDefinition()
    {
        foreach ($this->getEventDefinitions() as $eventDefinition) {
            if ($eventDefinition instanceof TerminateEventDefinition) {
                return $eventDefinition;
            }
        }
        return null;
    }
}
