<?php

namespace ProcessMaker\Nayra\Contracts\Bpmn;

/**
 * A container of flow elements (Process or SubProcess).
 */
interface FlowElementsContainerInterface extends EntityInterface
{
    /**
     * Get all nested flow elements.
     *
     * @return CollectionInterface|FlowElementInterface[]
     */
    public function getFlowElements();

    /**
     * Add a nested flow element.
     *
     * @param FlowElementInterface $element
     *
     * @return $this
     */
    public function addFlowElement(FlowElementInterface $element);

    /**
     * @return CollectionInterface|ActivityInterface[]
     */
    public function getActivities();

    /**
     * @return CollectionInterface|EventInterface[]
     */
    public function getEvents();

    /**
     * @return CollectionInterface|GatewayInterface[]
     */
    public function getGateways();

    /**
     * @return CollectionInterface
     */
    public function getArtifacts();

    /**
     * @return CollectionInterface
     */
    public function getLaneSets();
}
