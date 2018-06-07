<?php

namespace ProcessMaker\Nayra\Bpmn\Models;


use ProcessMaker\Nayra\Bpmn\BaseTrait;
use ProcessMaker\Nayra\Contracts\Bpmn\ArtifactInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\ProcessInterface;

/**
 * Class that implements the ArtifactInterface
 *
 * @package ProcessMaker\Nayra\Bpmn\Models
 */
class Artifact implements ArtifactInterface
{
    private $process;

    use BaseTrait;

    /**
     * Get the Process of the artifact.
     *
     * @return ProcessInterface
     */
    public function getProcess()
    {
        return $this->process;
    }

    /**
     * Set the Process of the artifact.
     *
     * @param ProcessInterface $process
     * @return $this
     */
    public function setProcess(ProcessInterface $process)
    {
        $this->process = $process;
        return $this;
    }
}
