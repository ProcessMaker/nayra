<?php

namespace ProcessMaker\Nayra\Bpmn\Models;

use ProcessMaker\Nayra\Bpmn\BaseTrait;
use ProcessMaker\Nayra\Bpmn\Collection;
use ProcessMaker\Nayra\Contracts\Bpmn\CollectionInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\InputSetInterface;

/**
 * An InputSet is a collection of DataInput elements that together define a
 * valid set of data inputs
 */
class InputSet implements InputSetInterface
{
    use BaseTrait;

    /**
     * Initialize input set.
     */
    protected function initInputSet()
    {
        $this->setDataInputs(new Collection);
    }

    /**
     * Get the DataInput elements that collectively make up this data requirement.
     *
     * @return DataInputInterface[]
     */
    public function getDataInputs()
    {
        return $this->getProperty(static::BPMN_PROPERTY_DATA_INPUTS);
    }

    /**
     * Set the DataInput elements that collectively make up this data requirement.
     *
     * @param CollectionInterface $dataInputs
     *
     * @return $this
     */
    public function setDataInputs(CollectionInterface $dataInputs)
    {
        return $this->setProperty(static::BPMN_PROPERTY_DATA_INPUTS, $dataInputs);
    }
}
