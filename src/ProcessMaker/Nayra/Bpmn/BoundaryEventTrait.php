<?php

namespace ProcessMaker\Nayra\Bpmn;

use ProcessMaker\Nayra\Bpmn\Models\ErrorEventDefinition;
use ProcessMaker\Nayra\Contracts\Bpmn\ActivityInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\BoundaryEventInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\CatchEventInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\ErrorEventDefinitionInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\ErrorInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\EventDefinitionInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\FlowInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\StateInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\TokenInterface;
use ProcessMaker\Nayra\Contracts\Engine\EngineInterface;
use ProcessMaker\Nayra\Contracts\Engine\ExecutionInstanceInterface;
use ProcessMaker\Nayra\Contracts\RepositoryInterface;

/**
 * Boundary event implementation.
 *
 * @see \ProcessMaker\Nayra\Contracts\Bpmn\BoundaryEventInterface
 * @package ProcessMaker\Nayra\Bpmn
 */
trait BoundaryEventTrait
{
    use CatchEventTrait;

    private $triggerPlace;

    /**
     * Build the transitions that define the element.
     *
     * @param RepositoryInterface $factory
     */
    public function buildTransitions(RepositoryInterface $factory)
    {
        $this->setRepository($factory);
        $this->triggerPlace = new State($this, CatchEventInterface::TOKEN_STATE_EVENT_CATCH);
        $this->transition = new Transition($this);
        $this->triggerPlace->connectTo($this->transition);

        $this->triggerPlace->attachEvent(State::EVENT_TOKEN_ARRIVED, function (TokenInterface $token) {
            $this->getRepository()
                ->getTokenRepository()
                ->persistCatchEventMessageArrives($this, $token);
            $this->notifyEvent(BoundaryEventInterface::EVENT_BOUNDARY_EVENT_CATCH, $this, $token);
        });

        $this->transition->attachEvent(Transition::EVENT_AFTER_TRANSIT, function ($transition, Collection $tokens) {
            foreach($tokens as $token) {
                $this->getRepository()
                    ->getTokenRepository()
                    ->persistCatchEventMessageConsumed($this, $token);
                $this->notifyEvent(BoundaryEventInterface::EVENT_BOUNDARY_EVENT_CONSUMED, $this, $token);

                $this->getProcess()->getEngine()->nextState(function () use($token) {
                    // Cancel the attachedTo activity
                    if ($this->getCancelActivity()) {
                        foreach ($this->getAttachedTo()->getTokens($token->getInstance()) as $token) {
                            $token->setStatus(ActivityInterface::TOKEN_STATE_CLOSED);
                        }
                    }
                });
            }
        });
    }

    /**
     * Get an input to the element. Boundary event does not have an input place.
     *
     * @param \ProcessMaker\Nayra\Contracts\Bpmn\FlowInterface|null $targetFlow
     *
     * @return StateInterface
     */
    public function getInputPlace(FlowInterface $targetFlow = null)
    {
        return null;
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
        if ($instance !== null && $this->getAttachedTo()->getActiveState()->getTokens($instance)->count() > 0) {
            // with a new token in the trigger place, the event catch element will be fired
            $this->triggerPlace->addNewToken($instance);
        }
        return $this;
    }

    /**
     * Register the BPMN elements with the engine.
     *
     * @param EngineInterface $engine
     *
     * @return FlowElementInterface
     */
    public function registerWithEngine(EngineInterface $engine)
    {
        // Register Catch Events, except Errors that will be catch through EVENT_ACTIVITY_EXCEPTION
        foreach ($this->getEventDefinitions() as $eventDefinition) {
            if ($eventDefinition instanceof ErrorEventDefinitionInterface) {
                continue;
            }
            $eventDefinition->registerWithCatchEvent($engine, $this);
        }
        // Schedule timer events when Activity is Activated
        $this->getAttachedTo()->attachEvent(ActivityInterface::EVENT_ACTIVITY_ACTIVATED, function (ActivityInterface $activity, TokenInterface $token) {
            $this->activateCatchEvent($token);
        });
        // Catch EVENT_ACTIVITY_EXCEPTION
        $this->getAttachedTo()->attachEvent(ActivityInterface::EVENT_ACTIVITY_EXCEPTION, function (ActivityInterface $activity, TokenInterface $token) {
            $error = $token->getProperty(ErrorEventDefinitionInterface::BPMN_PROPERTY_ERROR);
            $this->catchErrorEvent($token, $error);
        });
        return $this;
    }

    /**
     * Catch an error event message
     *
     * @param \ProcessMaker\Nayra\Contracts\Bpmn\TokenInterface $token
     * @param \ProcessMaker\Nayra\Contracts\Bpmn\ErrorInterface|null $error
     */
    private function catchErrorEvent(TokenInterface $token, ErrorInterface $error = null)
    {
        $errorDef = new ErrorEventDefinition();
        $error ? $errorDef->setError($error) : null;
        foreach ($this->getEventDefinitions() as $eventDefinition) {
            if ($eventDefinition instanceof ErrorEventDefinitionInterface
                && $eventDefinition->shouldCatchEventDefinition($errorDef)) {
                $this->triggerPlace->addNewToken($token->getInstance());
            }
        }
    }

    /**
     * Denotes whether the Activity should be cancelled or not.
     *
     * @return bool
     */
    public function getCancelActivity()
    {
        return $this->getProperty(BoundaryEventInterface::BPMN_PROPERTY_CANCEL_ACTIVITY, true);
    }

    /**
     * Set if the Activity should be cancelled or not.
     *
     * @param bool $cancelActivity
     *
     * @return BoundaryEventInterface
     */
    public function setCancelActivity($cancelActivity)
    {
        return $this->setProperty(BoundaryEventInterface::BPMN_PROPERTY_CANCEL_ACTIVITY, $cancelActivity);
    }

    /**
     * Get the Activity that boundary Event is attached to.
     *
     * @return ActivityInterface
     */
    public function getAttachedTo()
    {
        return $this->getProperty(BoundaryEventInterface::BPMN_PROPERTY_ATTACHED_TO);
    }

    /**
     * Set the Activity that boundary Event is attached to.
     *
     * @param ActivityInterface $activity
     *
     * @return BoundaryEventInterface
     */
    public function setAttachedTo(ActivityInterface $activity)
    {
        return $this->setProperty(BoundaryEventInterface::BPMN_PROPERTY_ATTACHED_TO, $activity);
    }
}
