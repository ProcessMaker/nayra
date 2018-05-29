<?php

namespace ProcessMaker\Nayra\Contracts\Bpmn;

/**
 * The Call Activity acts as a ‘wrapper’ for the invocation of a global
 * Process or Global Task within the execution.
 *
 * @package ProcessMaker\Nayra\Contracts\Bpmn
 */
interface CallActivityInterface extends ActivityInterface
{
    const BPMN_PROPERTY_CALLED_ELEMENT = 'calledElement';

    /**
     * Get the element to be called.
     *
     * @return ProcessInterface
     */
    public function getCalledElement();

    /**
     * Set the called element by the activity.
     *
     * @param \ProcessMaker\Nayra\Contracts\Bpmn\CallableElementInterface $callableElement
     *
     * @return $this
     */
    public function setCalledElement(CallableElementInterface $callableElement);
}
