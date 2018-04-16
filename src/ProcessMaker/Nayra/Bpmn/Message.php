<?php

namespace ProcessMaker\Nayra\Bpmn;

use ProcessMaker\Nayra\Contracts\Bpmn\ItemDefinitionInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\MessageInterface;

/**
 * Message class
 *
 */
class Message implements MessageInterface
{

    /**
     * @var string $name
     */
    private $name;

    /**
     * @var ItemDefinitionInterface $item
     */
    private $item;

    /**
     *
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     *
     *
     * @return ItemDefinitionInterface
     */
    public function getItem()
    {
        return $this->item;
    }

    /**
     *
     *
     * @return ItemDefinitionInterface
     */
    public function setItem(ItemDefinitionInterface $item)
    {
        $this->item = $item;
        return $this;
    }
}
