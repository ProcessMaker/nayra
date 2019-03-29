<?php

namespace ProcessMaker\Nayra\Contracts\Bpmn;

/**
 * Activity interface.
 *
 * @package ProcessMaker\Nayra\Contracts\Bpmn
 */
interface ActivityInterface extends FlowNodeInterface
{
    /**
     * Events defined for Activity
     */
    const EVENT_ACTIVITY_ACTIVATED = 'ActivityActivated';
    const EVENT_ACTIVITY_COMPLETED = 'ActivityCompleted';
    const EVENT_ACTIVITY_EXCEPTION = 'ActivityException';
    const EVENT_ACTIVITY_CLOSED = 'ActivityClosed';
    const EVENT_EVENT_TRIGGERED = 'EventTriggered';

    /**
     * Token states defined for Activity
     */
    const TOKEN_STATE_ACTIVE = 'ACTIVE';
    const TOKEN_STATE_FAILING = 'FAILING';
    const TOKEN_STATE_COMPLETED = 'COMPLETED';
    const TOKEN_STATE_CLOSED = 'CLOSED';

    /**
     * Get Process of the activity.
     *
     * @return ProcessInterface
     */
    public function getProcess();

    /**
     * Get Process of the activity.
     *
     * @param ProcessInterface $process
     *
     * @return ProcessInterface
     */
    public function setProcess(ProcessInterface $process);

    /**
     * Complete the activity instance identified by the token.
     *
     * @param TokenInterface $token
     *
     * @return $this
     */
    public function complete(TokenInterface $token);
}
