<?php

namespace ProcessMaker\Nayra\Bpmn;

use ProcessMaker\Nayra\Contracts\Bpmn\DataStoreInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\CollectionInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\FlowNodeInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\StateInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\TransitionInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\ActivityInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\TokenInterface;
use ProcessMaker\Nayra\Contracts\Repositories\RepositoryFactoryInterface;
use ProcessMaker\Nayra\Exceptions\ActivityWorkException;

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
    private $activateState;

    /**
     *
     * @var \ProcessMaker\Nayra\Contracts\Bpmn\TransitionInterface
     */
    private $activityTransition;

    /**
     * Build the transitions that define the element.
     *
     * @param RepositoryFactoryInterface $factory
     */
    public function buildTransitions(RepositoryFactoryInterface $factory)
    {
        $this->setFactory($factory);
        $this->activateState = new State($this);
        $this->activityTransition = new ActivityTransition($this);

        $this->activateState->connectTo($this->activityTransition);

        $this->activateState->attachEvent(
            StateInterface::EVENT_TOKEN_ARRIVED,
            function () {
                $this->fireEvent(ActivityInterface::EVENT_ACTIVITY_ACTIVATED, $this);
                try {
                    $this->work();
                } catch (ActivityWorkException $exception) {
                    $this->fireEvent(ActivityInterface::EVENT_ACTIVITY_EXCEPTION, $this, $exception);
                }
            }
        );
        $this->activityTransition->attachEvent(
            TransitionInterface::EVENT_AFTER_TRANSIT,
            function () {
                $this->fireEvent(ActivityInterface::EVENT_ACTIVITY_CLOSED, $this);

            }
        );
        $this->activateState->attachEvent(
            StateInterface::EVENT_TOKEN_CONSUMED,
            function () {
                $this->fireEvent(ActivityInterface::EVENT_ACTIVITY_COMPLETED, $this);

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
        $this->addInput($this->activateState);
        return $this->activateState;
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
        $this->activityTransition->connectTo($target->getInputPlace());
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
        $token->setProperty('STATUS', 'COMPLETED');
        return $this;
    }

    /**
     * Get tokens in the task.
     *
     * @param \ProcessMaker\Nayra\Contracts\Bpmn\DataStoreInterface $dataStore
     *
     * @return CollectionInterface
     */
    public function getTokens(DataStoreInterface $dataStore)
    {
        return $this->activateState->getTokens();
    }
}
