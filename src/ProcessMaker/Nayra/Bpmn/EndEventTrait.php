<?php

namespace ProcessMaker\Nayra\Bpmn;

use ProcessMaker\Nayra\Contracts\Bpmn\CollectionInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\ErrorEventDefinitionInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\EventInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\FlowNodeInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\StateInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\TerminateEventDefinitionInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\ThrowEventInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\TokenInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\TransitionInterface;
use ProcessMaker\Nayra\Contracts\FactoryInterface;
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
     * @var EndTransition|TerminateTransition
     */
    private $transition;

    /**
     * Build the transitions that define the element.
     *
     * @param FactoryInterface $factory
     */
    public function buildTransitions(FactoryInterface $factory)
    {
        $this->setFactory($factory);
        $this->endState = new State($this, EventInterface::TOKEN_STATE_ACTIVE);
        $terminate = $this->findTerminateEventDefinition();
        if ($terminate) {
            $this->transition = new TerminateTransition($this);
            $this->transition->setEventDefinition($terminate);
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
        //Create an input state
        $input = new State($this);
        $transition = new Transition($this, false);
        $input->connectTo($transition);
        $transition->connectTo($this->endState);
        $this->addInput($input);

        //if the element has event definition and those event definition have a payload we notify them
        //of the triggered event
        $this->endState->attachEvent(State::EVENT_TOKEN_ARRIVED, function (TokenInterface $token) {
            $this->notifyEvent(ThrowEventInterface::EVENT_THROW_TOKEN_ARRIVES, $this, $token);
            foreach($this->getEventDefinitions() as $eventDefinition) {
                $eventDefinitionClass = get_class($eventDefinition);
                $payload = method_exists($eventDefinition, 'getPayload') ? $eventDefinition->getPayload() : null;
                $this->notifyEvent($eventDefinitionClass::EVENT_THROW_EVENT_DEFINITION, $this, $token, $payload);
                $messageFlow = $payload ? $payload->getMessageFlow() : false;
                if ($messageFlow) {
                    $collaboration = $messageFlow->getCollaboration();
                    $collaboration->send($eventDefinition, $token);
                }
            }
        });
        $this->endState->attachEvent(State::EVENT_TOKEN_CONSUMED, function (TokenInterface $token) {
            $this->notifyEvent(ThrowEventInterface::EVENT_THROW_TOKEN_CONSUMED, $this, $token);
        });

        return $input;
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
     * @return \ProcessMaker\Nayra\Contracts\Bpmn\EventDefinitionInterface|null
     */
    private function findTerminateEventDefinition()
    {
        foreach ($this->getEventDefinitions() as $eventDefinition) {
            if ($eventDefinition instanceof TerminateEventDefinitionInterface
                || $eventDefinition instanceof ErrorEventDefinitionInterface) {
                return $eventDefinition;
            }
        }
        return null;
    }
}
