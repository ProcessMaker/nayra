<?php

namespace ProcessMaker\Nayra\Bpmn;

use ProcessMaker\Nayra\Contracts\Bpmn\ProcessInterface;

/**
 * Flow Element is an abstract class for all the elements that can appear in
 * a Process.
 */
trait FlowElementTrait
{
    use BaseTrait;

    /**
     * Owner process.
     *
     * @var ProcessInterface
     */
    private $ownerProcess;

    /**
     * Get the owner process.
     *
     * @return ProcessInterface
     */
    public function getOwnerProcess()
    {
        return $this->ownerProcess;
    }

    /**
     * Set the owner process.
     *
     * @param ProcessInterface $ownerProcess
     *
     * @return $this
     */
    public function setOwnerProcess(ProcessInterface $ownerProcess)
    {
        $this->ownerProcess = $ownerProcess;

        return $this;
    }
}
