<?php

namespace ProcessMaker\Nayra\Contracts\Bpmn;

use ProcessMaker\Nayra\Contracts\Engine\ExecutionInstanceInterface;

/**
 * Rule that defines if a flow node can be transit.
 *
 * @package ProcessMaker\Nayra\Contracts\Bpmn
 */
interface TransitionInterface extends ConnectionNodeInterface
{
    const EVENT_BEFORE_TRANSIT = 'BeforeTransit';
    const EVENT_AFTER_CONSUME = 'AfterConsume';
    const EVENT_AFTER_TRANSIT = 'AfterTransit';
    const EVENT_CONDITIONED_TRANSITION = 'ConditionedTransition';

    /**
     * Execute a transition.
     *
     * @param \ProcessMaker\Nayra\Contracts\Engine\ExecutionInstanceInterface $executionInstance
     *
     * @return bool
     */
    public function execute(ExecutionInstanceInterface $executionInstance);

    /**
     * Evaluates if the transition condition evaluates to true using the data of the execution instance
     *
     * @param TokenInterface|null $token
     * @param ExecutionInstanceInterface|null $executionInstance
     *
     * @return mixed
     */
    public function assertCondition(TokenInterface $token = null, ExecutionInstanceInterface $executionInstance = null);

    /**
     * Get transition owner element
     *
     * @return \ProcessMaker\Nayra\Contracts\Bpmn\FlowElementInterface
     */
    public function getOwner();

    /**
     * Find all the paths that complies with the $condition and $while.
     *
     * @param callable $condition
     * @param callable $while
     * @param array $path
     * @param array $passedthru
     * @param array $paths
     *
     * @return Collection
     */
    public function paths(callable $condition, callable $while, $path = [], &$passedthru = [], &$paths = []);
}
