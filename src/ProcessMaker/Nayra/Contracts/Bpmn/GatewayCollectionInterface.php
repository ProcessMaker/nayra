<?php

namespace ProcessMaker\Nayra\Contracts\Bpmn;

/**
 * Collection of gateways.
 */
interface GatewayCollectionInterface extends CollectionInterface
{
    /**
     * Add an element to the collection.
     *
     * @param GatewayInterface $element
     *
     * @return $this
     */
    public function add(GatewayInterface $element);

    /**
     * Get a item by index
     *
     * @param $index
     *
     * @return GatewayInterface
     */
    public function item($index);
}
