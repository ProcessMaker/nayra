<?php

namespace ProcessMaker\Models;

use ProcessMaker\Nayra\Bpmn\Collection;
use ProcessMaker\Nayra\Bpmn\StartEventTrait;
use ProcessMaker\Nayra\Contracts\Bpmn\StartEventInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\CatchEventInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\DataOutputAssociationInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\DataOutputInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\EventDefinitionInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\EventInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\MessageListenerInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\OutputSetInterface;

/**
 * Start Event implementation.
 *
 * @package ProcessMaker\Models
 */
class StartEvent implements StartEventInterface, CatchEventInterface, MessageListenerInterface
{

    use StartEventTrait,
        LocalFlowNodeTrait,
        LocalProcessTrait,
        LocalPropertiesTrait;

    private $parallelMultiple;
    private $dataOutputs;
    private $dataOutputAssociations;
    private $outputSet;
    private $triggerPlace;
    private $eventDefinitions;


    public function initStartEvent()
    {
        $this->dataOutputAssociations= new Collection;
        $this->dataOutputs= new Collection;
        $this->eventDefinitions= new Collection;
    }
    /**
     * Array map of custom event classes for the bpmn element.
     *
     * @return array
     */
    protected function getBpmnEventClasses()
    {
        return [];
    }

    /**
     * Get true when all of the types of triggers that are listed in the
     * catch Event MUST be triggered before the Process is instantiated.
     *
     * @return boolean
     */
    public function isParallelMultiple()
    {
        return $this->parallelMultiple;
    }

    /**
     * Get Data Outputs for the catch Event.
     *
     * @return DataOutputInterface[]
     */
    public function getDataOutputs()
    {
        return $this->dataOutputs;
    }

    /**
     * Get Data Associations of the catch Event.
     *
     * @return DataOutputAssociationInterface[]
     */
    public function getDataOutputAssociations()
    {
        return $this->dataOutputAssociations;
    }

    /**
     * Get OutputSet for the catch Event.
     *
     * @return OutputSetInterface
     */
    public function getOutputSet()
    {
        return $this->outputSet;
    }

    /**
     * Get EventDefinitions that are triggers expected for a catch Event.
     *
     * @return EventDefinitionInterface[]
     */
    public function getEventDefinitions()
    {
        return $this->eventDefinitions;
    }
}
