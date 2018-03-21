<?php

namespace ProcessMaker\Models;

use ProcessMaker\Nayra\Contracts\Bpmn\ProcessInterface;

/**
 * Trait to store processes as local varable.
 *
 * @package ProcessMaker\Models
 */
trait LocalProcessTrait
{
    /**
     *
     * @var Process
     */
    private $process;

    /**
     * Get process.
     *
     * @return Process
     */
    public function getProcess()
    {
        return $this->process;
    }

    /**
     * Set the process.
     *
     * @param \ProcessMaker\Nayra\Contracts\Bpmn\ProcessInterface $process
     * @return $this
     */
    public function setProcess(ProcessInterface $process)
    {
        $this->process = $process;
        return $this;
    }
}
