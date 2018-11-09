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
     * @param ExecutionInstanceInterface $executionInstance
     * @return mixed
     */
    public function assertCondition(TokenInterface $token = null, ExecutionInstanceInterface $executionInstance);
}
