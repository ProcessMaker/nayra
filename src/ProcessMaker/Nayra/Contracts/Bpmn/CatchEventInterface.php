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
     * Get EventDefinitions that are triggers expected for a catch Event.
     *
     * @return EventDefinitionInterface[]
     */
    public function getEventDefinitions();

    /**
     * @return \ProcessMaker\Nayra\Engine\ExecutionInstance[]
     */
    public function getTargetInstances(EventDefinitionInterface $message, TokenInterface $token);
}
