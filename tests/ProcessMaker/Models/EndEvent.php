<?php

namespace ProcessMaker\Models;

use ProcessMaker\Nayra\Bpmn\EndEventTrait;
use ProcessMaker\Nayra\Contracts\Bpmn\CatchEventInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\DataInputAssociationInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\DataInputInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\EndEventInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\EventDefinitionInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\InputSetInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\MessageListenerInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\ThrowEventInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\TokenInterface;

/**
 * End event implementation.
 *
 * @package ProcessMaker\Models
 */
class EndEvent implements EndEventInterface, ThrowEventInterface
{

    use EndEventTrait,
        LocalFlowNodeTrait,
        LocalProcessTrait,
        LocalPropertiesTrait;

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
     * @return \ProcessMaker\Nayra\Engine\ExecutionInstance[]
     */
    public function getTargetInstances(EventDefinitionInterface $message, TokenInterface $token)
    {
        // TODO: Implement getTargetInstances() method.
    }

    /**
     * Get Data Inputs for the throw Event.
     *
     * @return DataInputInterface[]
     */
    public function getDataInputs()
    {
        // TODO: Implement getDataInputs() method.
    }

    /**
     * Get Data Associations of the throw Event.
     *
     * @return DataInputAssociationInterface[]
     */
    public function getDataInputAssociations()
    {
        // TODO: Implement getDataInputAssociations() method.
    }

    /**
     * Get InputSet for the throw Event.
     *
     * @return InputSetInterface
     */
    public function getInputSet()
    {
        // TODO: Implement getInputSet() method.
    }
}
