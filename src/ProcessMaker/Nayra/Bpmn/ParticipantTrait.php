<?php

namespace ProcessMaker\Nayra\Bpmn;

use ProcessMaker\Nayra\Contracts\Bpmn\ProcessInterface;

/**
 * Participant class
 *
 */
trait ParticipantTrait
{

    use BaseTrait;
    /**
     * @var \ProcessMaker\Nayra\Contracts\Bpmn\ProcessInterface $process
     */
    private $process;

    /**
     * @var mixed[] $interfaces
     */
    private $interfaces;

    /**
     * @var mixed[] $endPoints
     */
    private $endPoints;

    /**
     * @var array $participantMultiplicity
     */
    private $participantMultiplicity = [
        'maximum' => 1,
        'minimum' => 0,
    ];

    /**
     * Returns the process associated to the participant
     *
     * @return \ProcessMaker\Nayra\Contracts\Bpmn\ProcessInterface
     */
    public function getProcess()
    {
        return $this->process;
    }

    /**
     * Set the Process that the Participant uses in the Collaboration.
     *
     * @param ProcessInterface
     *
     * @return $this
     */
    public function setProcess(ProcessInterface $process)
    {
        $this->process = $process;
        return $this;
    }

    /**
     *
     *
     * @return mixed[]
     */
    public function getInterfaces()
    {
        return $this->interfaces;
    }

    /**
     *
     *
     * @return mixed[]
     */
    public function getEndPoints()
    {
        return $this->endPoints;
    }

    /**
     * Get Participant multiplicity for a given interaction.
     *
     * @return array
     */
    public function getParticipantMultiplicity()
    {
        return $this->participantMultiplicity;
    }

    /**
     * Set Participant multiplicity for a given interaction.
     *
     * @param int $maximum
     * @param int $minimum
     *
     * @return $this
     */
    public function setParticipantMultiplicity($maximum, $minimum)
    {
        $this->participantMultiplicity['maximum'] = $maximum;
        $this->participantMultiplicity['minimum'] = $minimum;
        return $this;
    }
}
