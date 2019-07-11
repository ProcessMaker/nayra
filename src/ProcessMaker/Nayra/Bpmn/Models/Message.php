<?php

namespace ProcessMaker\Nayra\Bpmn\Models;

use ProcessMaker\Nayra\Bpmn\BaseTrait;
use ProcessMaker\Nayra\Contracts\Bpmn\ItemDefinitionInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\MessageFlowInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\MessageInterface;

/**
 * Message implementation.
 *
 */
class Message implements MessageInterface
{
    use BaseTrait;

    /**
     * @var string $id
     */
    private $id;

    /**
     * @var string $name
     */
    private $name;

    /**
     * @var ItemDefinitionInterface $item
     */
    private $item;

    /**
     * @var MessageFlowInterface $messageFlow
     */
    private $messageFlow;

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
     * Allows to set the item
     *
     * @param ItemDefinitionInterface $item
     *
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
     * @param string $value
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
