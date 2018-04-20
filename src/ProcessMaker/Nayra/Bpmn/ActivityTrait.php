<?php

namespace ProcessMaker\Nayra\Bpmn;

use ProcessMaker\Nayra\Bpmn\State;
use ProcessMaker\Nayra\Bpmn\Transition;
use ProcessMaker\Nayra\Contracts\Bpmn\ActivityInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\CollectionInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\FlowNodeInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\StateInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\TokenInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\TransitionInterface;
use ProcessMaker\Nayra\Contracts\Repositories\RepositoryFactoryInterface;

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
     * Build the transitions that define the element.
     *
     * @param RepositoryFactoryInterface $factory
     */
    public function buildTransitions(RepositoryFactoryInterface $factory)
    {
        $this->setFactory($factory);
        $this->activeState = new State($this, ActivityInterface::TOKEN_STATE_ACTIVE);
        $this->activityTransition = new ActivityTransition($this);
        $this->failingState = new State($this, ActivityInterface::TOKEN_STATE_FAILING);
        $this->exceptionTransition = new ExceptionTransition($this);
        $this->closeExceptionTransition = new CloseExceptionTransition($this);
        $this->transition = new Transition($this);
        $this->closedState = new State($this, ActivityInterface::TOKEN_STATE_COMPLETED);

        $this->activeState->connectTo($this->exceptionTransition);
        $this->activeState->connectTo($this->activityTransition);
        $this->failingState->connectTo($this->closeExceptionTransition);
        $this->exceptionTransition->connectTo($this->failingState);
        $this->activityTransition->connectTo($this->closedState);
        $this->closedState->connectTo($this->transition);
        $this->closeExceptionTransition->connectTo($this->closedState);

        $this->activeState->attachEvent(
            StateInterface::EVENT_TOKEN_ARRIVED,
            function (TokenInterface $token) {
                $this->notifyEvent(ActivityInterface::EVENT_ACTIVITY_ACTIVATED, $this, $token);

            }
        );
        $this->transition->attachEvent(
            TransitionInterface::EVENT_BEFORE_TRANSIT,
            function (TransitionInterface $transition) {
                $this->notifyEvent(ActivityInterface::EVENT_ACTIVITY_CLOSED, $this, $transition);

            }
        );
        $this->failingState->attachEvent(
            StateInterface::EVENT_TOKEN_ARRIVED,
            function (TokenInterface $token) {
                $this->notifyEvent(ActivityInterface::EVENT_ACTIVITY_EXCEPTION, $this, $token);

            }
        );
        $this->closedState->attachEvent(
            StateInterface::EVENT_TOKEN_ARRIVED,
            function (TokenInterface $token) {
                $this->notifyEvent(ActivityInterface::EVENT_ACTIVITY_COMPLETED, $this, $token);

            }
        );

    }

    /**
     * Get an input to the element.
     *
     * @return \ProcessMaker\Nayra\Contracts\Bpmn\StateInterface
     */
    public function getInputPlace()
    {
        $this->addInput($this->activeState);
        return $this->activeState;
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
     * Get tokens in the activity.
     *
     * @return CollectionInterface
     */
    public function getTokens()
    {
        $tokens = $this->activeState->getTokens()->toArray();
        $tokens = array_merge($tokens, $this->failingState->getTokens()->toArray());
        $tokens = array_merge($tokens, $this->closedState->getTokens()->toArray());
        return new Collection($tokens);
    }
}
