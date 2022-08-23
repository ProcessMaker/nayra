<?php

namespace ProcessMaker\Nayra\Contracts\Bpmn;

use ProcessMaker\Nayra\Contracts\RepositoryInterface;

/**
 * Behavior that must implement all event messages
 */
interface MessageInterface extends EntityInterface
{
    const BPMN_PROPERTY_ITEM = 'item';

    const BPMN_PROPERTY_ITEM_REF = 'itemRef';

    /**
     * Get the ItemDefinition is used to define the payload of the Message.
     *
     * @return ItemDefinitionInterface
     */
    public function getItem();

    /**
     * Returns the id of the message
     *
     * @return string
     */
    public function getId();

    /**
     * Sets the id of the message
     *
     * @param string $value
     */
    public function setId($value);

    /**
     * Returns the name of the message
     *
     * @return string
     */
    public function getName();

    /**
     * Allows to set the item
     *
     * @param ItemDefinitionInterface $item
     *
     * @return $this
     */
    public function setItem(ItemDefinitionInterface $item);
}
