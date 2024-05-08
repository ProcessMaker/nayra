<?php

namespace ProcessMaker\Nayra\Bpmn;

use Exception;
use ProcessMaker\Nayra\Contracts\Bpmn\ActivityInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\BoundaryEventInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\ErrorEventDefinitionInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\FlowInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\LoopCharacteristicsInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\StateInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\TokenInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\TransitionInterface;
use ProcessMaker\Nayra\Contracts\RepositoryInterface;

/**
 * Activity behavior's implementation.
 */
trait ActivityTrait
{
    use FlowNodeTrait;

    /**
     * @var \ProcessMaker\Nayra\Contracts\Bpmn\StateInterface
     */
    private $activeState;

    /**
     * @var \ProcessMaker\Nayra\Contracts\Bpmn\StateInterface
     */
    private $interruptingEventState;

    /**
     * @var \ProcessMaker\Nayra\Contracts\Bpmn\StateInterface
     */
    private $interruptedState;

    /**
     * @var \ProcessMaker\Nayra\Contracts\Bpmn\TransitionInterface
     */
    private $completedTransition;

    /**
     * @var \ProcessMaker\Nayra\Contracts\Bpmn\StateInterface
     */
    private $failingState;

    /**
     * @var \ProcessMaker\Nayra\Contracts\Bpmn\TransitionInterface
     */
    private $exceptionTransition;

    /**
     * @var \ProcessMaker\Nayra\Contracts\Bpmn\TransitionInterface
     */
    private $closeExceptionTransition;

    /**
     * @var \ProcessMaker\Nayra\Contracts\Bpmn\TransitionInterface
     */
    private $transition;

    /**
     * @var \ProcessMaker\Nayra\Contracts\Bpmn\StateInterface
     */
    private $completedState;

    /**
     * @var \ProcessMaker\Nayra\Contracts\Bpmn\TransitionInterface
     */
    private $skippedTransition;

    /**
     * @var LoopCharacteristicsTransition
     */
    private $loopTransition;

    /**
     * @var \ProcessMaker\Nayra\Contracts\Bpmn\StateInterface
     */
    private $caughtInterruptionState;

    /**
     * @var \ProcessMaker\Nayra\Contracts\Bpmn\StateInterface
     */
    private $waitInterruptState;

    /**
     * @var ActivityInterruptedTransition
     */
    private $activityInterruptedTransition;

    /**
     * Build the transitions that define the element.
     *
     * @param RepositoryInterface $factory
     */
    public function buildTransitions(RepositoryInterface $factory)
    {
        $this->setRepository($factory);
        $this->activeState = new State($this, ActivityInterface::TOKEN_STATE_ACTIVE);
        $this->failingState = new State($this, ActivityInterface::TOKEN_STATE_FAILING);
        $this->completedState = new State($this, ActivityInterface::TOKEN_STATE_COMPLETED);
        $this->interruptedState = new State($this, ActivityInterface::TOKEN_STATE_INTERRUPTED);
        $this->caughtInterruptionState = new State($this, ActivityInterface::TOKEN_STATE_CAUGHT_INTERRUPTION);
        $this->interruptingEventState = new State($this, ActivityInterface::TOKEN_STATE_EVENT_INTERRUPTING_EVENT);
        $this->waitInterruptState = new State($this, ActivityInterface::TOKEN_STATE_WAIT_INTERRUPT);

        $this->activityInterruptedTransition = new ActivityInterruptedTransition($this, true);
        $this->activityInterruptedTransition->attachEvent(
            TransitionInterface::EVENT_BEFORE_TRANSIT,
            function ($transition, $consumedTokens) {
                foreach ($consumedTokens as $token) {
                    $previousState = $token->getOwner()->getName();
                    if ($previousState !== ActivityInterface::TOKEN_STATE_EVENT_INTERRUPTING_EVENT) {
                        $token->setStatus(ActivityInterface::TOKEN_STATE_CLOSED);
                        $this->getRepository()
                            ->getTokenRepository()
                            ->persistActivityClosed($this, $token);
                    }
                }
            }
        );

        $this->activityExceptionTransition = new ActivityExceptionTransition($this, true);
        $this->boundaryCaughtTransition = new BoundaryCaughtTransition($this, true);
        $this->closeCanceledActivity = new UncaughtCancelTransition($this, true);
        $this->boundaryExceptionTransition = new BoundaryExceptionTransition($this, true);
        $this->boundaryCancelActivityTransition = new Transition($this, true);

        $this->completedTransition = new ActivityCompletedTransition($this, true);
        $this->cancelActiveTransition = new CancelActivityTransition($this, true);
        $this->exceptionTransition = new ExceptionTransition($this, true);
        $this->closeExceptionTransition = new CloseExceptionTransition($this, true);
        $this->completeExceptionTransition = new CompleteExceptionTransition($this, true);
        $this->transition = new DataOutputTransition($this, false);
        $this->loopTransition = new LoopCharacteristicsTransition($this, false);
        $this->completedState->connectTo($this->loopTransition);
        $this->loopTransition->connectTo($this->activeState);
        $this->skippedTransition = new SkipActivityTransition($this, false);

        $this->interruptedState->connectTo($this->activityExceptionTransition);
        $this->interruptedState->connectTo($this->boundaryCaughtTransition);
        $this->interruptedState->connectTo($this->closeCanceledActivity);

        $this->boundaryCaughtTransition->connectTo($this->caughtInterruptionState);
        $this->caughtInterruptionState->connectTo($this->boundaryCancelActivityTransition);
        $this->waitInterruptState->connectTo($this->boundaryCancelActivityTransition);

        $this->activeState->connectTo($this->activityInterruptedTransition);
        $this->completedState->connectTo($this->activityInterruptedTransition);
        $this->interruptingEventState->connectTo($this->activityInterruptedTransition);
        $this->activityInterruptedTransition->connectTo($this->interruptedState);

        $this->activeState->connectTo($this->exceptionTransition);
        $this->activeState->connectTo($this->completedTransition);
        $this->activeState->connectTo($this->cancelActiveTransition);
        $this->activityExceptionTransition->connectTo($this->failingState);
        $this->failingState->connectTo($this->completeExceptionTransition);
        $this->failingState->connectTo($this->closeExceptionTransition);
        $this->failingState->connectTo($this->boundaryExceptionTransition);
        $this->cancelActiveTransition->connectTo($this->interruptedState);
        $this->exceptionTransition->connectTo($this->interruptedState);
        $this->completedTransition->connectTo($this->completedState);
        $this->completedState->connectTo($this->transition);
        $this->completeExceptionTransition->connectTo($this->completedState);

        $this->activeState->attachEvent(
            StateInterface::EVENT_TOKEN_ARRIVED,
            function (TokenInterface $token, TransitionInterface $source) {
                try {
                    $this->getRepository()
                    ->getTokenRepository()
                    ->persistActivityActivated($this, $token);
                    $this->notifyEvent(ActivityInterface::EVENT_ACTIVITY_ACTIVATED, $this, $token);
                } catch (Exception $exception) {
                    $token->setStatus(ActivityInterface::TOKEN_STATE_FAILING);
                    $token->logError($exception, $this);
                }
            }
        );
        $this->failingState->attachEvent(
            StateInterface::EVENT_TOKEN_ARRIVED,
            function (TokenInterface $token) {
                $this->getRepository()
                    ->getTokenRepository()
                    ->persistActivityException($this, $token);
                $error = $token->getProperty(ActivityInterface::BPMN_PROPERTY_ERROR, null);
                $this->notifyEvent(ActivityInterface::EVENT_ACTIVITY_EXCEPTION, $this, $token, $error);
            }
        );
        $this->completedState->attachEvent(
            StateInterface::EVENT_TOKEN_ARRIVED,
            function (TokenInterface $token) {
                $loop = $this->getLoopCharacteristics();
                if ($loop && $loop->isExecutable()) {
                    $loop->onTokenCompleted($token);
                }
                $this->getRepository()
                    ->getTokenRepository()
                    ->persistActivityCompleted($this, $token);
                $this->notifyEvent(ActivityInterface::EVENT_ACTIVITY_COMPLETED, $this, $token);
            }
        );
        $this->closeExceptionTransition->attachEvent(
            TransitionInterface::EVENT_AFTER_CONSUME,
            function ($transition, $tokens) {
                $loop = $this->getLoopCharacteristics();
                foreach ($tokens as $token) {
                    if ($loop && $loop->isExecutable() && $loop->isDataInputValid($token->getInstance(), $token)) {
                        $loop->onTokenTerminated($token);
                    }
                    $this->getRepository()
                        ->getTokenRepository()
                        ->persistActivityCompleted($this, $token);
                }
                $this->notifyEvent(ActivityInterface::EVENT_ACTIVITY_CANCELLED, $this, $transition, $tokens);
            }
        );
        $this->boundaryExceptionTransition->attachEvent(
            TransitionInterface::EVENT_AFTER_CONSUME,
            function ($transition, $tokens) {
                foreach ($tokens as $token) {
                    $token->setStatus(ActivityInterface::TOKEN_STATE_CLOSED);
                    $this->getRepository()
                        ->getTokenRepository()
                        ->persistActivityCompleted($this, $token);
                }
            }
        );
        $this->boundaryCancelActivityTransition->attachEvent(
            TransitionInterface::EVENT_AFTER_CONSUME,
            function ($transition, $tokens) {
                $boundaryEvents = $this->getBoundaryEvents();
                foreach ($tokens as $token) {
                    foreach ($boundaryEvents as $boundaryEvent) {
                        $caughtEventId = $token->getProperty(TokenInterface::BPMN_PROPERTY_EVENT_ID);
                        foreach ($boundaryEvent->getEventDefinitions() as $eventDefinition) {
                            if ($caughtEventId === $eventDefinition->getPayload()->getId()) {
                                $boundaryEvent->notifyInternalEvent($token);
                                break 3;
                            }
                        }
                    }
                }
                $this->notifyEvent(ActivityInterface::EVENT_ACTIVITY_CANCELLED, $this, $transition, $tokens);
            }
        );
        $this->boundaryExceptionTransition->attachEvent(
            TransitionInterface::EVENT_AFTER_CONSUME,
            function ($transition, $tokens) {
                $boundaryEvents = $this->getBoundaryEvents();
                foreach ($tokens as $token) {
                    foreach ($boundaryEvents as $boundaryEvent) {
                        foreach ($boundaryEvent->getEventDefinitions() as $eventDefinition) {
                            if ($eventDefinition instanceof ErrorEventDefinitionInterface) {
                                $boundaryEvent->notifyInternalEvent($token);
                                break 3;
                            }
                        }
                    }
                }
                $this->notifyEvent(ActivityInterface::EVENT_ACTIVITY_CANCELLED, $this, $transition, $tokens);
            }
        );
        $this->closeCanceledActivity->attachEvent(
            TransitionInterface::EVENT_AFTER_CONSUME,
            function ($transition, $tokens) {
                $loop = $this->getLoopCharacteristics();
                foreach ($tokens as $token) {
                    if ($loop && $loop->isExecutable()) {
                        $loop->onTokenTerminated($token);
                    }
                    $token->setStatus(ActivityInterface::TOKEN_STATE_CLOSED);
                    $this->getRepository()
                        ->getTokenRepository()
                        ->persistActivityCompleted($this, $token);
                    $this->notifyEvent(ActivityInterface::EVENT_ACTIVITY_CANCELLED, $this, $transition, $tokens);
                }
            }
        );
        $this->loopTransition->attachEvent(
            TransitionInterface::EVENT_AFTER_CONSUME,
            function (TransitionInterface $transition, Collection $consumedTokens) {
                if (!$this->loopTransition->shouldCloseTokens()) {
                    return;
                }
                foreach ($consumedTokens as $token) {
                    $token->setStatus(ActivityInterface::TOKEN_STATE_CLOSED);
                    $this->getRepository()
                        ->getTokenRepository()
                        ->persistActivityClosed($this, $token);
                    $this->notifyEvent(ActivityInterface::EVENT_ACTIVITY_CLOSED, $this, $token);
                }
            }
        );
    }

    /**
     * Get an input to the element.
     *
     * @param \ProcessMaker\Nayra\Contracts\Bpmn\FlowInterface|null $targetFlow
     *
     * @return \ProcessMaker\Nayra\Contracts\Bpmn\StateInterface
     */
    public function getInputPlace(FlowInterface $targetFlow = null)
    {
        $ready = new State($this, ActivityInterface::TOKEN_STATE_READY);
        $ready->connectTo($this->activityInterruptedTransition);
        $transition = new DataInputTransition($this, false);
        $invalidDataInput = new InvalidDataInputTransition($this, false);
        $ready->connectTo($transition);
        $ready->connectTo($invalidDataInput);
        $ready->connectTo($this->skippedTransition);
        $transition->connectTo($this->activeState);
        $invalidDataInput->connectTo($this->failingState);
        $this->addInput($ready);

        return $ready;
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
        $target = $targetFlow->getTarget();
        $place = $target->getInputPlace($targetFlow);
        $this->transition->connectTo($place);
        $this->transition->attachEvent(
            TransitionInterface::EVENT_AFTER_CONSUME,
            function (TransitionInterface $transition, Collection $consumedTokens) {
                foreach ($consumedTokens as $token) {
                    $token->setStatus(ActivityInterface::TOKEN_STATE_CLOSED);
                    $this->getRepository()
                        ->getTokenRepository()
                        ->persistActivityClosed($this, $token);
                    $this->notifyEvent(ActivityInterface::EVENT_ACTIVITY_CLOSED, $this, $token);
                }
            }
        );
        $this->skippedTransition->connectTo($place);

        return $this;
    }

    /**
     * Complete the activity instance.
     *
     * @param TokenInterface $token
     *
     * @return $this;
     */
    public function complete(TokenInterface $token)
    {
        $token->setStatus(ActivityInterface::TOKEN_STATE_COMPLETED);

        return $this;
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

    /**
     * @return LoopCharacteristicsInterface
     */
    public function getLoopCharacteristics()
    {
        return $this->getProperty(ActivityInterface::BPMN_PROPERTY_LOOP_CHARACTERISTICS);
    }

    /**
     * @param LoopCharacteristicsInterface $loopCharacteristics
     *
     * @return static
     */
    public function setLoopCharacteristics(LoopCharacteristicsInterface $loopCharacteristics)
    {
        return $this->setProperty(ActivityInterface::BPMN_PROPERTY_LOOP_CHARACTERISTICS, $loopCharacteristics);
    }

    /**
     * Get the boundary events attached to the activity
     *
     * @return \ProcessMaker\Nayra\Contracts\Bpmn\BoundaryEventInterface[]|\ProcessMaker\Nayra\Contracts\Bpmn\CollectionInterface
     */
    public function getBoundaryEvents()
    {
        $boundaryElements = [];
        $process = $this->getProcess();
        if ($process) {
            $events = $process->getEvents();
            foreach ($events as $event) {
                if ($event instanceof BoundaryEventInterface && $event->getAttachedTo() === $this) {
                    $boundaryElements[] = $event;
                }
            }
        }

        return new Collection($boundaryElements);
    }

    /**
     * Notify an event to the element.
     *
     * @param TokenInterface $token
     */
    public function notifyInterruptingEvent(TokenInterface $token)
    {
        $instance = $token->getInstance();
        $properties = $token->getProperties();
        unset($properties[TokenInterface::BPMN_PROPERTY_ID]);
        $this->interruptingEventState->addNewToken($instance, $properties);
        $this->waitInterruptState->addNewToken($instance, $properties);

        return $this;
    }
}
