<?php

namespace ProcessMaker\Nayra\Bpmn\Models;

use ProcessMaker\Nayra\Bpmn\BaseTrait;
use ProcessMaker\Nayra\Contracts\Bpmn\ItemDefinitionInterface;

/**
 * ItemDefinition class
 *
 */
class ItemDefinition implements ItemDefinitionInterface
{

    use BaseTrait;

    /**
     * Get the nature of the Item. Possible values are physical
     * or information.
     *
     * @return string
     */
    public function getItemKind()
    {
        return $this->getProperty(ItemDefinitionInterface::BPMN_PROPERTY_ITEM_KIND);
    }

    /**
     * Get the concrete data structure to be used.
     *
     * @return mixed
     */
    public function getStructure()
    {
        return $this->getProperty(ItemDefinitionInterface::BPMN_PROPERTY_STRUCTURE);
    }

    public function setStructure($structure)
    {
        return $this->setProperty(ItemDefinitionInterface::BPMN_PROPERTY_STRUCTURE, $structure);
    }

    /**
     * Get true if the data structure represents a collection.
     *
     * @return boolean
     */
    public function isCollection()
    {
        return $this->getProperty(ItemDefinitionInterface::BPMN_PROPERTY_IS_COLLECTION);
    }
}
