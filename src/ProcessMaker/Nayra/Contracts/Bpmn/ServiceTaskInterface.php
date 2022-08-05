<?php

namespace ProcessMaker\Nayra\Contracts\Bpmn;

/**
 * Defines the interface to be used by the ScriptTasks
 */
interface ServiceTaskInterface extends ActivityInterface
{
    const BPMN_PROPERTY_IMPLEMENTATION = 'implementation';

    const EVENT_SERVICE_TASK_ACTIVATED = 'ServiceTaskActivated';

    /**
     * Sets the service task implementation
     *
     * @param mixed $implementation
     *
     * @return $this
     */
    public function setImplementation($implementation);

    /**
     * Returns the service task implementation
     *
     * @return mixed
     */
    public function getImplementation();

    /**
     * Runs the Service Task
     *
     * @param TokenInterface $token
     *
     * @return $this
     */
    public function run(TokenInterface $token);
}
