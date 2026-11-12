<?php

namespace ProcessMaker\Nayra\Contracts\Bpmn;

/**
 * Flow Element is an abstract class for all the elements that can appear in
 * a Process.
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

    /**
     * Immediate container (Process or SubProcess), when nested.
     *
     * @return FlowElementsContainerInterface|null
     */
    public function getContainer();

    /**
     * @param FlowElementsContainerInterface|null $container
     *
     * @return $this
     */
    public function setContainer($container);
}
