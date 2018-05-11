<?php

namespace ProcessMaker\Nayra\Contracts\Bpmn;

use ProcessMaker\Nayra\Contracts\Repositories\RepositoryFactoryInterface;

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
     * @param $value
     */
    public function setId($value);

    /**
     * @return RepositoryFactoryInterface
     */
    public function getFactory();

    /**
     * @param RepositoryFactoryInterface $factory
     * @return $this
     */
    public function setFactory(RepositoryFactoryInterface $factory);

    /**
     * Returns the name of the message
     *
     * @return string
     */
    public function getName();

    /**
     * Sets the message flow to which this message pertains
     *
     * @param $messageFlow
     *
     * @return mixed
     */
    public function setMessageFlow($messageFlow);


    /**
     * Returns the message flow to which this message pertains
     *
     * @return mixed
     */
    public function getMessageFlow();
}
