<?php

namespace ProcessMaker\Nayra\Bpmn\Models;

use ProcessMaker\Nayra\Bpmn\BaseTrait;
use ProcessMaker\Nayra\Bpmn\Collection;
use ProcessMaker\Nayra\Contracts\Bpmn\CollectionInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\OutputSetInterface;

/**
 * An OutputSet is a collection of DataOutputs elements that together can be
 * produced as output.
 */
class OutputSet implements OutputSetInterface
{
    use BaseTrait;

    /**
     * Initialize input set.
     */
    protected function initInputSet()
    {
        $this->setDataOutputs(new Collection);
    }

    /**
     * Get DataOutput elements that MAY collectively be outputted.
     *
     * @return DataOutputInterface[]
     */
    public function getDataOutputs()
    {
        return $this->getProperty(static::BPMN_PROPERTY_DATA_OUTPUTS);
    }

    /**
     * Set DataOutput elements that MAY collectively be outputted.
     *
     * @param CollectionInterface $dataOutputs
     *
     * @return $this
     */
    public function setDataOutputs(CollectionInterface $dataOutputs)
    {
        return $this->setProperty(static::BPMN_PROPERTY_DATA_OUTPUTS, $dataOutputs);
    }
}
