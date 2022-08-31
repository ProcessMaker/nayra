<?php

namespace ProcessMaker\Nayra\Bpmn;

use ProcessMaker\Nayra\Contracts\Bpmn\ParticipantInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\ProcessInterface;

/**
 * Participant class
 */
trait ParticipantTrait
{
    use BaseTrait;

    /**
     * @var mixed[]
     */
    private $interfaces;

    /**
     * @var mixed[]
     */
    private $endPoints;

    /**
     * Initialize the default values for the participant element.
     */
    protected function initParticipant()
    {
        $default = ['maximum' => 1, 'minimum' => 0];
        $this->setProperty(ParticipantInterface::BPMN_PROPERTY_PARTICIPANT_MULTIPICITY, $default);
    }

    /**
     * Returns the process associated to the participant
     *
     * @return \ProcessMaker\Nayra\Contracts\Bpmn\ProcessInterface
     */
    public function getProcess()
    {
        return $this->getProperty(ParticipantInterface::BPMN_PROPERTY_PROCESS);
    }

    /**
     * Set the Process that the Participant uses in the Collaboration.
     *
     * @param ProcessInterface $process
     *
     * @return $this
     */
    public function setProcess(ProcessInterface $process)
    {
        $this->setProperty(ParticipantInterface::BPMN_PROPERTY_PROCESS, $process);
        $process->addProperty(ProcessInterface::BPMN_PROPERTY_PARTICIPANT, $this);

        return $this;
    }

    /**
     * Get Participant multiplicity for a given interaction.
     *
     * @return array
     */
    public function getParticipantMultiplicity()
    {
        return $this->getProperty(ParticipantInterface::BPMN_PROPERTY_PARTICIPANT_MULTIPICITY);
    }

    /**
     * Set Participant multiplicity for a given interaction.
     *
     * @param array $array
     *
     * @return $this
     */
    public function setParticipantMultiplicity(array $array)
    {
        return $this->setProperty(ParticipantInterface::BPMN_PROPERTY_PARTICIPANT_MULTIPICITY, $array);
    }
}
