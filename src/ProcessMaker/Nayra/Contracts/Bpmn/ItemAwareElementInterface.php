<?php

namespace ProcessMaker\Nayra\Contracts\Bpmn;

/**
 * This element can store or convey items during process execution.
 *
 * @package ProcessMaker\Nayra\Contracts\Bpmn
 */
interface ItemAwareElementInterface extends FlowElementInterface
{

    /**
     * Get the items that are stored or conveyed by the ItemAwareElement.
     *
     * @return ItemDefinitionInterface
     */
    public function getItemSubject();
}
