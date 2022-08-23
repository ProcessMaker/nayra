<?php

namespace ProcessMaker\Nayra\Bpmn\Models;

use ProcessMaker\Nayra\Bpmn\EndEventTrait;
use ProcessMaker\Nayra\Contracts\Bpmn\EndEventInterface;
use ProcessMaker\Nayra\Model\DataInputAssociationInterface;
use ProcessMaker\Nayra\Model\DataInputInterface;
use ProcessMaker\Nayra\Model\EventDefinitionInterface;
use ProcessMaker\Nayra\Model\InputSetInterface;
use ProcessMaker\Nayra\Model\TokenInterface;

/**
 * Class EndEvent
 *
 * @codeCoverageIgnore
 */
class EndEvent implements EndEventInterface
{
    use EndEventTrait;

    private $dataInputs;

    private $dataInputAssociations;

    private $inputSet;

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
     * @param EventDefinitionInterface $message
     * @param TokenInterface $token
     * @return \ProcessMaker\Nayra\Contracts\Engine\ExecutionInstanceInterface[]
     */
    public function getTargetInstances(EventDefinitionInterface $message, TokenInterface $token)
    {
        return $this->getOwnerProcess()->getInstances();
    }

    /**
     * Get Data Inputs for the throw Event.
     *
     * @return DataInputInterface[]
     */
    public function getDataInputs()
    {
        return $this->dataInputs;
    }

    /**
     * Get Data Associations of the throw Event.
     *
     * @return DataInputAssociationInterface[]
     */
    public function getDataInputAssociations()
    {
        return $this->getDataInputAssociations();
    }

    /**
     * Get InputSet for the throw Event.
     *
     * @return InputSetInterface
     */
    public function getInputSet()
    {
        return $this->inputSet;
    }
}
