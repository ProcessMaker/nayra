<?php

namespace ProcessMaker\Nayra\Contracts\Bpmn;

interface MessageInterface extends EntityInterface
{

    /**
     * Get the ItemDefinition is used to define the payload of the Message.
     *
     * @return ItemDefinitionInterface
     */
    public function getItem();
}
