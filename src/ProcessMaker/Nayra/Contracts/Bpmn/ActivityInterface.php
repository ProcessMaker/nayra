<?php

namespace ProcessMaker\Nayra\Contracts\Bpmn;

/**
 * Activity interface.
 */
interface ActivityInterface extends FlowNodeInterface
{
    /**
     * Events defined for Activity
     */
    const EVENT_ACTIVITY_ACTIVATED = 'ActivityActivated';

    const EVENT_ACTIVITY_COMPLETED = 'ActivityCompleted';

    const EVENT_ACTIVITY_EXCEPTION = 'ActivityException';

    const EVENT_ACTIVITY_CANCELLED = 'ActivityCancelled';

    const EVENT_ACTIVITY_CLOSED = 'ActivityClosed';

    const EVENT_ACTIVITY_SKIPPED = 'ActivitySkipped';

    /**
     * Properties and composed elements
     */
    const BPMN_PROPERTY_LOOP_CHARACTERISTICS = 'loopCharacteristics';

    const BPMN_PROPERTY_IO_SPECIFICATION = 'ioSpecification';

    const BPMN_PROPERTY_ERROR = 'error';

    /**
     * Token states defined for Activity
     */
    const TOKEN_STATE_READY = 'READY';

    const TOKEN_STATE_ACTIVE = 'ACTIVE';

    const TOKEN_STATE_FAILING = 'FAILING';

    const TOKEN_STATE_COMPLETED = 'COMPLETED';

    const TOKEN_STATE_CLOSED = 'CLOSED';

    const TOKEN_STATE_INTERRUPTED = 'INTERRUPTED';

    const TOKEN_STATE_CAUGHT_INTERRUPTION = 'CAUGHT_INTERRUPTION';

    const TOKEN_STATE_EVENT_INTERRUPTING_EVENT = 'INTERRUPTING_EVENT';

    const TOKEN_STATE_WAIT_INTERRUPT = 'WAIT_INTERRUPT';

    const TOKEN_STATE_SKIPPED = 'SKIPPED';

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

    /**
     * Get the active state of the element
     *
     * @return StateInterface
     */
    public function getActiveState();

    /**
     * Get loop characteristics of the activity
     *
     * @return LoopCharacteristicsInterface
     */
    public function getLoopCharacteristics();

    /**
     * Get loop characteristics of the activity
     *
     * @param \ProcessMaker\Nayra\Contracts\Bpmn\LoopCharacteristicsInterface $loopCharacteristics
     *
     * @return LoopCharacteristicsInterface
     */
    public function setLoopCharacteristics(LoopCharacteristicsInterface $loopCharacteristics);

    /**
     * Get the boundary events attached to the activity
     *
     * @return \ProcessMaker\Nayra\Contracts\Bpmn\BoundaryEventInterface[]|\ProcessMaker\Nayra\Contracts\Bpmn\CollectionInterface
     */
    public function getBoundaryEvents();

    /**
     * Notify an event to the element.
     *
     * @param TokenInterface $token
     */
    public function notifyInterruptingEvent(TokenInterface $token);
}
