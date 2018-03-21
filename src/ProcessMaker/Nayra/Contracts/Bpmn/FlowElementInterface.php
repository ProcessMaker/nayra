<?php

namespace ProcessMaker\Nayra\Contracts\Bpmn;

/**
 * Flow Element is an abstract class for all the elements that can appear in
 * a Process.
 *
 * @package ProcessMaker\Nayra\Contracts\Bpmn
 */
interface FlowElementInterface extends EntityInterface
{
    /**
     * @return $this
     */
    public function setOwnerProcess(ProcessInterface $ownerProcess);

    /**
     * @return ProcessInterface
     */
    public function getOwnerProcess();
}