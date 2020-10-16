<?php

namespace ProcessMaker\Nayra\Bpmn\Models;

use ProcessMaker\Nayra\Bpmn\BaseTrait;
use ProcessMaker\Nayra\Contracts\Bpmn\ItemDefinitionInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\MessageFlowInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\SignalInterface;

/**
 * Implementation of signal class.
 *
 * @package \ProcessMaker\Nayra\Bpmn\Models
 */
class Signal implements SignalInterface
{
    use BaseTrait;

    /**
     * @var string $id
     */
    private $id;

    /**
     * @var string $id
     */
    private $name;

    /**
     * @var ItemDefinitionInterface $item
     */
    private $item;


    /** @var MessageFlowInterface */
    private $messageFlow;

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

    /**
     * Sets the name of the signal
     *
     * @param string $value
     */
    public function setName($value)
    {
        $this->name = $value;
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

}
