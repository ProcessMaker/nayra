<?php

namespace ProcessMaker\Nayra\Bpmn;

use ProcessMaker\Nayra\Contracts\Bpmn\ItemDefinitionInterface;

/**
 * ItemDefinition class
 *
 */
class ItemDefinition implements ItemDefinitionInterface
{
    /**
     * @var string
     */
    private $itemKind;

    /**
     * @var mixed
     */
    private $structure;

    /**
     * @var boolean
     */
    private $isCollection;

    /**
     * Get the nature of the Item. Possible values are physical
     * or information.
     *
     * @return string
     */
    public function getItemKind()
    {
        return $this->itemKind;
    }

    /**
     * Get the concrete data structure to be used.
     *
     * @return mixed
     */
    public function getStructure()
    {
        return $this->structure;
    }

    /**
     * Get true if the data structure represents a collection.
     *
     * @return boolean
     */
    public function isCollection()
    {
        return $this->isCollection;
    }
}
