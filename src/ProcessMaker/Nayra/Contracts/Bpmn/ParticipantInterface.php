<?php

namespace ProcessMaker\Nayra\Contracts\Bpmn;

/**
 *
 */
interface ParticipantInterface extends EntityInterface
{

    /**
     * Get the Process that the Participant uses in the Collaboration.
     *
     * @return ProcessInterface
     */
    public function getProcess();

    /**
     * Set the Process that the Participant uses in the Collaboration.
     *
     * @param ProcessInterface
     *
     * @return $this
     */
    public function setProcess(ProcessInterface $process);

    /**
     * Get Interfaces that a Participant supports.
     *
     * @return array
     */
    public function getInterfaces();

    /**
     *
     */
    public function getParticipantMultiplicity();

}
