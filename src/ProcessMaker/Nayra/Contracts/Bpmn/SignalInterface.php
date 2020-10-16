<?php

namespace ProcessMaker\Nayra\Contracts\Bpmn;

/**
 * Behavior that must implement all event messages
 *
 * @package ProcessMaker\Nayra\Contracts\Bpmn
 */
interface SignalInterface extends EntityInterface
{
    /**
     * Returns the id of the message
     *
     * @return string
     */
    public function getId();

    /**
     * Sets the id of the message
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
     * Sets the name of the signal
     * @param string $value
     */
    public function setName($value);

    /**
     * Get the ItemDefinition is used to define the payload of the Signal.
     *
     * @return ItemDefinitionInterface
     */
    public function getItem();

    /**
     * Allows to set the item
     *
     * @param ItemDefinitionInterface $item
     *
     * @return $this
     */
    public function setItem(ItemDefinitionInterface $item);
}
