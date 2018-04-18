<?php

namespace ProcessMaker\Models;

use ProcessMaker\Nayra\Bpmn\Collection;
use ProcessMaker\Nayra\Bpmn\IntermediateCatchEventTrait;
use ProcessMaker\Nayra\Contracts\Bpmn\MessageListenerInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\IntermediateCatchEventInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\MessageEventDefinitionInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\MessageInterface;

/**
 * IntermediateThrowEvent implementation.
 *
 * @package ProcessMaker\Models
 */
class IntermediateCatchEvent implements IntermediateCatchEventInterface, MessageListenerInterface
{

    use IntermediateCatchEventTrait,
        LocalFlowNodeTrait,
        LocalProcessTrait,
        LocalPropertiesTrait;
    /**
     * @var \ProcessMaker\Nayra\Contracts\Bpmn\DataInputAssociationInterface[] $dataInputAssociations
     */
    private $dataOutputAssociations;

    /**
     * @var \ProcessMaker\Nayra\Contracts\Bpmn\DataInputInterface[]
     */
    private $dataOutputs;

    /**
     * @var \ProcessMaker\Nayra\Contracts\Bpmn\InputSetInterface
     */
    private $outputSet;

    /**
     * @var \ProcessMaker\Nayra\Contracts\Bpmn\EventDefinitionInterface[]
     */
    private $eventDefinitions;

    /**
     * If this value is true, then all of the types of triggers that are listed
     * in the catch Event MUST be triggered before the Process is instantiated.
     *
     * @var boolean $parallelMultiple
     */
    private $parallelMultiple = false;

    protected function initIntermediateThrowEvent()
    {
        $this->dataOutputAssociations= new Collection;
        $this->dataOutputs= new Collection;
        $this->eventDefinitions= new Collection;
    }

    protected function getBpmnEventClasses()
    {
        return [];
    }

    public function getDataOutputAssociations()
    {
        return $this->dataOutputAssociations;
    }

    public function getDataOutputs()
    {
        return $this->dataOutputs;
    }

    public function getEventDefinitions()
    {
        return $this->eventDefinitions;
    }

    public function getOutputSet()
    {
        return $this->outputSet;
    }

    public function isParallelMultiple()
    {
        return $this->parallelMultiple;
    }

    public function execute(MessageEventDefinitionInterface $message)
    {
        echo "listener...";
        //echo print_r($message->getProperties(), true);
    }
}
