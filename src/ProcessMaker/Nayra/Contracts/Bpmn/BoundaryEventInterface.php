<?php

namespace ProcessMaker\Nayra\Contracts\Bpmn;

/**
 * BoundaryEventInterface interface.
 */
interface BoundaryEventInterface extends CatchEventInterface
{
    const BPMN_PROPERTY_CANCEL_ACTIVITY = 'cancelActivity';

    const BPMN_PROPERTY_ATTACHED_TO = 'attachedTo';

    const BPMN_PROPERTY_ATTACHED_TO_REF = 'attachedToRef';

    const EVENT_BOUNDARY_EVENT_CATCH = 'BoundaryEventCatch';

    const EVENT_BOUNDARY_EVENT_CONSUMED = 'BoundaryEventConsumed';

    const TOKEN_STATE_DISPATCH = 'BoundaryEventDispatch';

    /**
     * Denotes whether the Activity should be cancelled or not.
     *
     * @return bool
     */
    public function getCancelActivity();

    /**
     * Set if the Activity should be cancelled or not.
     *
     * @param bool $cancelActivity
     *
     * @return BoundaryEventInterface
     */
    public function setCancelActivity($cancelActivity);

    /**
     * Get the Activity that boundary Event is attached to.
     *
     * @return ActivityInterface
     */
    public function getAttachedTo();

    /**
     * Set the Activity that boundary Event is attached to.
     *
     * @param ActivityInterface $activity
     *
     * @return BoundaryEventInterface
     */
    public function setAttachedTo(ActivityInterface $activity);
}
