<?php

namespace ProcessMaker\Models;

use ProcessMaker\Nayra\Bpmn\Collection;
use ProcessMaker\Nayra\Bpmn\IntermediateThrowEventTrait;
use ProcessMaker\Nayra\Contracts\Bpmn\IntermediateThrowEventInterface;

/**
 * IntermediateThrowEvent implementation.
 *
 * @package ProcessMaker\Models
 */
class IntermediateThrowEvent implements IntermediateThrowEventInterface
{

    use IntermediateThrowEventTrait,
        LocalFlowNodeTrait,
        LocalProcessTrait,
        LocalPropertiesTrait;
    /**
     * @var \ProcessMaker\Nayra\Contracts\Bpmn\DataInputAssociationInterface[] $dataInputAssociations
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

    protected function initIntermediateThrowEvent()
    {
        $this->dataInputAssociations= new Collection;
        $this->dataInputs= new Collection;
        $this->eventDefinitions= new Collection;
    }

    protected function getBpmnEventClasses()
    {
        return [];
    }

    public function getDataInputAssociations()
    {
        return $this->dataInputAssociations;
    }

    public function getDataInputs()
    {
        return $this->dataInputs;
    }

    public function getEventDefinitions()
    {
        return $this->eventDefinitions;
    }

    public function getInputSet()
    {
        return $this->inputSet;
    }
}
