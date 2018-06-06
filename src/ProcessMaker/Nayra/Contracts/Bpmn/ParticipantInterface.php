<?php

namespace ProcessMaker\Nayra\Contracts\Bpmn;

/**
 * Participant of a Collaboration.
 *
 * @package ProcessMaker\Nayra\Contracts\Bpmn
 */
interface ParticipantInterface extends EntityInterface
{
    const BPMN_PROPERTY_PROCESS = 'process';
    const BPMN_PROPERTY_PROCESS_REF = 'processRef';
    const BPMN_PROPERTY_PARTICIPANT_MULTIPICITY = 'participantMultiplicity';

    /**
     * Get the Process that the Participant uses in the Collaboration.
     *
     * @return ProcessInterface
     */
    public function getProcess();

    /**
     * Set the Process that the Participant uses in the Collaboration.
     *
     * @param ProcessInterface $process
     *
     * @return $this
     */
    public function setProcess(ProcessInterface $process);

    /**
     * Get Participant multiplicity for a given interaction.
     *
     * @return array
     */
    public function getParticipantMultiplicity();

    /**
     * Set Participant multiplicity for a given interaction.
     *
     * @param array $array
     *
     * @return $this
     */
    public function setParticipantMultiplicity(array $array);
}
