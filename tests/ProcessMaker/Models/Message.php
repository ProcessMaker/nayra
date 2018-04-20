<?php

namespace ProcessMaker\Models;

use ProcessMaker\Nayra\Contracts\Bpmn\ItemDefinitionInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\MessageInterface;
use ProcessMaker\Nayra\Contracts\Repositories\RepositoryFactoryInterface;

class Message implements MessageInterface
{
    /**
     * @var string $id
     */
    private $id;

    /**
     * @var string $name
     */
    private $name;

    /**
     * @var RepositoryFactoryInterface $factory
     */
    private $factory;

    /**
     * @var ItemDefinitionInterface $item
     */
    private $item;

    /**
     * @return RepositoryFactoryInterface
     */
    public function getFactory()
    {
        return $this->factory;
    }

    /**
     * @param RepositoryFactoryInterface $factory
     * @return $this
     */
    public function setFactory(RepositoryFactoryInterface $factory)
    {
        $this->factory = $factory;
    }

    /**
     * Get the ItemDefinition is used to define the payload of the Message.
     *
     * @return ItemDefinitionInterface
     */
    public function getItem()
    {
        return $this->item;
    }

    /**
     * Allosws to set the item
     * @param ItemDefinitionInterface $item
     * @return $this
     */
    public function setItem(ItemDefinitionInterface $item)
    {
        $this->item = $item;
        return $this;
    }

    /**
     * Returns the id of the message
     *
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Sets the id of the message
     * @param $value
     */
    public function setId($value)
    {
        $this->id = $value;
    }

    /**
     * Returns the name of the message
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }
}