<?php

namespace ProcessMaker\Nayra\Bpmn;

use ProcessMaker\Nayra\Contracts\Bpmn\ActivityInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\FlowNodeInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\StateInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\TokenInterface;
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
     * Build the transitions that define the element.
     *
     * @param RepositoryInterface $factory
     */
    public function buildTransitions(RepositoryInterface $factory)
    {
        $this->setFactory($factory);
        $this->activeState = new State($this, ActivityInterface::TOKEN_STATE_ACTIVE);
        $this->activityTransition = new ActivityTransition($this, true);
        $this->failingState = new State($this, ActivityInterface::TOKEN_STATE_FAILING);
        $this->exceptionTransition = new ExceptionTransition($this, true);
        $this->closeExceptionTransition = new CloseExceptionTransition($this, true);
        $this->transition = new Transition($this, true);
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
        $ready = new State($this);
        $transition = new Transition($this, false);
        $ready->connectTo($transition);
        $transition->connectTo($this->activeState);
        $this->addInput($ready);
        return $ready;
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
        $place = $target->getInputPlace();
        $this->transition->connectTo($place);
        $place->attachEvent(
            StateInterface::EVENT_TOKEN_CONSUMED,
            function (TokenInterface $token) {
                $token->setStatus(ActivityInterface::TOKEN_STATE_CLOSED);
                $this->notifyEvent(ActivityInterface::EVENT_ACTIVITY_CLOSED, $this, $token);

            }
        );
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
}
