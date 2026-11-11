<?php

namespace ProcessMaker\Nayra\Contracts\Bpmn;

/**
 * An Activity whose internal details are modeled with flow elements.
 */
interface SubProcessInterface extends ActivityInterface, FlowElementsContainerInterface
{
    const BPMN_PROPERTY_TRIGGERED_BY_EVENT = 'triggeredByEvent';

    /**
     * Whether this subprocess is started by an event (event subprocess).
     *
     * @return bool
     */
    public function getTriggeredByEvent();

    /**
     * @param bool $triggeredByEvent
     *
     * @return $this
     */
    public function setTriggeredByEvent($triggeredByEvent);

    /**
     * Find none start events used when entering an embedded subprocess.
     *
     * @return StartEventInterface[]
     */
    public function findStartEvents();

    /**
     * Validate subprocess structural rules.
     *
     * @return string[] empty when valid
     */
    public function validate();
}
