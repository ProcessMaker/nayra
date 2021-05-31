<?php

namespace ProcessMaker\Nayra\Bpmn;

use Exception;
use ProcessMaker\Nayra\Contracts\Bpmn\ActivityInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\FlowInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\LoopCharacteristicsInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\StateInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\TokenInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\TransitionInterface;
use ProcessMaker\Nayra\Contracts\RepositoryInterface;

/**
 * Activity behavior's implementation.
 *
 * @package ProcessMaker\Nayra\Bpmn
 */
trait ActivityTrait
{
    use FlowNodeTrait;

    /**
     *
     * @var \ProcessMaker\Nayra\Contracts\Bpmn\StateInterface
     */
    private $activeState;

    /**
     *
     * @var \ProcessMaker\Nayra\Contracts\Bpmn\TransitionInterface
     */
    private $activityTransition;

    /**
     *
     * @var \ProcessMaker\Nayra\Contracts\Bpmn\StateInterface
     */
    private $failingState;

    /**
     *
     * @var \ProcessMaker\Nayra\Contracts\Bpmn\TransitionInterface
     */
    private $exceptionTransition;

    /**
     *
     * @var \ProcessMaker\Nayra\Contracts\Bpmn\TransitionInterface
     */
    private $closeExceptionTransition;

    /**
     *
     * @var \ProcessMaker\Nayra\Contracts\Bpmn\TransitionInterface
     */
    private $transition;

    /**
     *
     * @var \ProcessMaker\Nayra\Contracts\Bpmn\StateInterface
     */
    private $closedState;

    /**
     *
     * @var \ProcessMaker\Nayra\Contracts\Bpmn\StateInterface
     */
    private $skippedState;

    /**
     *
     * @var \ProcessMaker\Nayra\Contracts\Bpmn\TransitionInterface
     */
    private $skippedTransition;

    /**
     * Build the transitions that define the element.
     *
     * @param RepositoryInterface $factory
     */
    public function buildTransitions(RepositoryInterface $factory)
    {
        $this->setRepository($factory);
        $this->activeState = new State($this, ActivityInterface::TOKEN_STATE_ACTIVE);
        $this->activityTransition = new ActivityTransition($this, true);
        $this->closeActiveTransition = new CloseExceptionTransition($this, true);
        $this->failingState = new State($this, ActivityInterface::TOKEN_STATE_FAILING);
        $this->exceptionTransition = new ExceptionTransition($this, true);
        $this->closeExceptionTransition = new CloseExceptionTransition($this, true);
        $this->completeExceptionTransition = new CompleteExceptionTransition($this, true);
        $this->transition = new DataOutputTransition($this, false);
        $this->closedState = new State($this, ActivityInterface::TOKEN_STATE_COMPLETED);
        $this->loopTransition = new LoopCharacteristicsTransition($this, false);
        $this->closedState->connectTo($this->loopTransition);
        $this->loopTransition->connectTo($this->activeState);
        $this->skippedState = new State($this, ActivityInterface::TOKEN_STATE_SKIPPED);
        $this->skippedTransition = new Transition($this, false);

        $this->activeState->connectTo($this->exceptionTransition);
        $this->activeState->connectTo($this->activityTransition);
        $this->activeState->connectTo($this->closeActiveTransition);
        $this->failingState->connectTo($this->completeExceptionTransition);
        $this->failingState->connectTo($this->closeExceptionTransition);
        $this->exceptionTransition->connectTo($this->failingState);
        $this->activityTransition->connectTo($this->closedState);
        $this->closedState->connectTo($this->transition);
        $this->completeExceptionTransition->connectTo($this->closedState);
        $this->skippedState->connectTo($this->skippedTransition);

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
                $this->notifyEvent(ActivityInterface::EVENT_ACTIVITY_EXCEPTION, $this, $token);
            }
        );
        $this->closedState->attachEvent(
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
        $this->skippedState->attachEvent(
            StateInterface::EVENT_TOKEN_ARRIVED,
            function (TokenInterface $token) {
                $this->notifyEvent(ActivityInterface::EVENT_ACTIVITY_SKIPPED, $this, $token);
            }
        );
        $this->closeExceptionTransition->attachEvent(
            TransitionInterface::EVENT_AFTER_CONSUME,
            function ($transition, $tokens) {
                $loop = $this->getLoopCharacteristics();
                foreach ($tokens as $token) {
                    if ($loop && $loop->isExecutable()) {
                        $loop->onTokenTerminated($token);
                    }
                    $this->getRepository()
                        ->getTokenRepository()
                        ->persistActivityCompleted($this, $token);
                }
                $this->notifyEvent(ActivityInterface::EVENT_ACTIVITY_CANCELLED, $this, $transition, $tokens);
            }
        );
        $this->closeActiveTransition->attachEvent(
            TransitionInterface::EVENT_AFTER_CONSUME,
            function ($transition, $tokens) {
                $loop = $this->getLoopCharacteristics();
                foreach ($tokens as $token) {
                    if ($loop && $loop->isExecutable()) {
                        $loop->onTokenTerminated($token);
                    }
                    $this->getRepository()
                        ->getTokenRepository()
                        ->persistActivityCompleted($this, $token);
                    $this->notifyEvent(ActivityInterface::EVENT_ACTIVITY_CANCELLED, $this, $transition, $tokens);
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
        $ready = new State($this, 'INCOMING');
        $transition = new DataInputTransition($this, false);
        $emptyDataInput = new EmptyDataInputTransition($this, false);
        $ready->connectTo($transition);
        $ready->connectTo($emptyDataInput);
        $transition->connectTo($this->activeState);
        $emptyDataInput->connectTo($this->skippedState);
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
}
