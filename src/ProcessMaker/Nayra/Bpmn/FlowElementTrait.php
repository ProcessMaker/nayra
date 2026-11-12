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
     * Immediate container (Process or SubProcess).
     *
     * @var \ProcessMaker\Nayra\Contracts\Bpmn\FlowElementsContainerInterface|null
     */
    private $container;

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

    /**
     * Get the immediate flow-elements container.
     *
     * @return \ProcessMaker\Nayra\Contracts\Bpmn\FlowElementsContainerInterface|null
     */
    public function getContainer()
    {
        return $this->container;
    }

    /**
     * Set the immediate flow-elements container.
     *
     * @param \ProcessMaker\Nayra\Contracts\Bpmn\FlowElementsContainerInterface|null $container
     *
     * @return $this
     */
    public function setContainer($container)
    {
        $this->container = $container;

        return $this;
    }
}
