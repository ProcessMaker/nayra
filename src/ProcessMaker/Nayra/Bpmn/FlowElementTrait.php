<?php

namespace ProcessMaker\Nayra\Bpmn;

use ProcessMaker\Nayra\Contracts\Bpmn\ProcessInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\TransitionInterface;

/**
 * Flow Element is an abstract class for all the elements that can appear in
 * a Process.
 *
 * @package ProcessMaker\Nayra\Bpmn
 */
trait FlowElementTrait
{
    use EntityTrait;

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
