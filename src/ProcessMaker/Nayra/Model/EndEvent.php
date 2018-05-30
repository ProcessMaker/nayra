<?php

namespace ProcessMaker\Nayra\Model;


use ProcessMaker\Nayra\Bpmn\EndEventTrait;
use ProcessMaker\Nayra\Contracts\Bpmn\EndEventInterface;

/**
 * Class EndEvent
 *
 * @package ProcessMaker\Nayra\Model
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
