<?php

namespace ProcessMaker\Nayra\Contracts\Bpmn;

use ProcessMaker\Nayra\Contracts\Repositories\StorageInterface;

/**
 * Behavior that must implement all event messages
 *
 * @package ProcessMaker\Nayra\Contracts\Bpmn
 */
interface MessageInterface
{
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
     * @return StorageInterface
     */
    public function getFactory();

    /**
     * @param StorageInterface $factory
     * @return $this
     */
    public function setFactory(StorageInterface $factory);

    /**
     * Returns the name of the message
     *
     * @return string
     */
    public function getName();

    /**
     * Sets the message flow to which this message pertains
     *
     * @param MessageFlowInterface $messageFlow
     *
     * @return mixed
     */
    public function setMessageFlow(MessageFlowInterface $messageFlow);


    /**
     * Returns the message flow to which this message pertains
     *
     * @return mixed
     */
    public function getMessageFlow();

    /**
     * Allows to set the item
     *
     * @param ItemDefinitionInterface $item
     *
     * @return $this
     */
    public function setItem(ItemDefinitionInterface $item);
}
