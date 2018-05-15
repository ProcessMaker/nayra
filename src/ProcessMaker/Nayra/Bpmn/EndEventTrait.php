<?php

namespace ProcessMaker\Nayra\Bpmn;

use ProcessMaker\Nayra\Contracts\Bpmn\CollectionInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\EventDefinitionInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\EventInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\FlowNodeInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\GatewayInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\StateInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\TransitionInterface;
use ProcessMaker\Nayra\Contracts\Engine\ExecutionInstanceInterface;
use ProcessMaker\Nayra\Contracts\Repositories\RepositoryFactoryInterface;
use ProcessMaker\Nayra\Exceptions\InvalidSequenceFlowException;

/**
 * End event behavior's implementation.
 *
 * @package ProcessMaker\Nayra\Bpmn
 */
trait EndEventTrait
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

    /**
     * Build the transitions that define the element.
     *
     * @param RepositoryFactoryInterface $factory
     */
    public function buildTransitions(RepositoryFactoryInterface $factory)
    {
        $this->setFactory($factory);
        $this->endState = new State($this, EventInterface::TOKEN_STATE_ACTIVE);
        $this->transition = new EndTransition($this);
        $this->endState->connectTo($this->transition);
        $this->transition->attachEvent(
            TransitionInterface::EVENT_AFTER_TRANSIT,
            function (TransitionInterface $transition, CollectionInterface $consumeTokens) {
                $this->notifyEvent(EventInterface::EVENT_EVENT_TRIGGERED, $this, $transition, $consumeTokens);
            }
        );

        $this->triggerPlace = new State($this, GatewayInterface::TOKEN_STATE_INCOMMING);
        $this->triggerPlace->connectTo($this->transition);
    }

    /**
     * Get an input to the element.
     *
     * @return StateInterface
     */
    public function getInputPlace()
    {
        $this->addInput($this->endState);
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
     * Method to be called when a message event arrives
     *
     * @param EventDefinitionInterface $message
     * @param ExecutionInstanceInterface $instance
     *
     * @return $this
     */
    public function execute(EventDefinitionInterface $message, ExecutionInstanceInterface $instance = null)
    {
        //the instance will be null just in start events, so we don't process it
        if ($instance !== null) {
            // with a new token in the trigger place, the event catch element will be fired
            $this->triggerPlace->addNewToken($instance);
        }
    }
}
