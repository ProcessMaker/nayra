<?php

namespace ProcessMaker\Nayra\Bpmn\Models;

use ProcessMaker\Nayra\Bpmn\Collection;
use ProcessMaker\Nayra\Bpmn\IntermediateThrowEventTrait;
use ProcessMaker\Nayra\Contracts\Bpmn\IntermediateThrowEventInterface;

/**
 * IntermediateThrowEvent implementation.
 */
class IntermediateThrowEvent implements IntermediateThrowEventInterface
{
    use IntermediateThrowEventTrait;

    /**
     * @var \ProcessMaker\Nayra\Contracts\Bpmn\DataInputAssociationInterface[]
     */
    private $dataInputAssociations;

    /**
     * @var \ProcessMaker\Nayra\Contracts\Bpmn\DataInputInterface[]
     */
    private $dataInputs;

    /**
     * @var \ProcessMaker\Nayra\Contracts\Bpmn\InputSetInterface
     */
    private $inputSet;

    /**
     * @var \ProcessMaker\Nayra\Contracts\Bpmn\EventDefinitionInterface[]
     */
    private $eventDefinitions;

    /**
     * Initialize intermediate throw event.
     */
    protected function initIntermediateThrowEvent()
    {
        $this->dataInputAssociations = new Collection;
        $this->dataInputs = new Collection;
        $this->setProperty(static::BPMN_PROPERTY_EVENT_DEFINITIONS, new Collection);
    }

    /**
     * Get BPMN event classes.
     *
     * @return array
     */
    protected function getBpmnEventClasses()
    {
        return [];
    }

    /**
     * Get data input associations.
     *
     * @return \ProcessMaker\Nayra\Contracts\Bpmn\CollectionInterface
     */
    public function getDataInputAssociations()
    {
        return $this->dataInputAssociations;
    }

    /**
     * Get data inputs.
     *
     * @return \ProcessMaker\Nayra\Contracts\Bpmn\CollectionInterface
     */
    public function getDataInputs()
    {
        return $this->dataInputs;
    }

    /**
     * Get event definitions.
     *
     * @return \ProcessMaker\Nayra\Contracts\Bpmn\CollectionInterface
     */
    public function getEventDefinitions()
    {
        return $this->getProperty(static::BPMN_PROPERTY_EVENT_DEFINITIONS);
    }

    /**
     * Get input set.
     *
     * @return \ProcessMaker\Nayra\Contracts\Bpmn\InputSetInterface
     */
    public function getInputSet()
    {
        return $this->inputSet;
    }
}
