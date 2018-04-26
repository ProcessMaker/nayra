<?php

namespace ProcessMaker\Nayra\Contracts\Bpmn;

/**
 * CatchEvent interface.
 *
 * @package ProcessMaker\Nayra\Contracts\Bpmn
 */
interface CatchEventInterface extends EventInterface
{

    /**
     * Get true when all of the types of triggers that are listed in the
     * catch Event MUST be triggered before the Process is instantiated.
     *
     * @return boolean
     */
    public function isParallelMultiple();

    /**
     * Get Data Outputs for the catch Event.
     *
     * @return DataOutputInterface[]
     */
    public function getDataOutputs();

    /**
     * Get Data Associations of the catch Event.
     *
     * @return DataOutputAssociationInterface[]
     */
    public function getDataOutputAssociations();

    /**
     * Get OutputSet for the catch Event.
     *
     * @return OutputSetInterface
     */
    public function getOutputSet();

    /**
     * Get EventDefinitions that are triggers expected for a catch Event.
     *
     * @return EventDefinitionInterface[]
     */
    public function getEventDefinitions();

    /**
     * @return \ProcessMaker\Nayra\Engine\ExecutionInstance[]
     */
    public function getTargetInstances(MessageEventDefinitionInterface $message, TokenInterface $token);
}
