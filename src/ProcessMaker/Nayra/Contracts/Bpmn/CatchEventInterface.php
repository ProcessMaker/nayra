<?php

namespace ProcessMaker\Nayra\Contracts\Bpmn;

use ProcessMaker\Nayra\Contracts\Engine\EngineInterface;

/**
 * CatchEvent interface.
 *
 * @package ProcessMaker\Nayra\Contracts\Bpmn
 */
interface CatchEventInterface extends EventInterface
{
    const BPMN_PROPERTY_PARALLEL_MULTIPLE = 'parallelMultiple';

    const TOKEN_STATE_EVENT_CATCH = 'EVENT_CATCH';
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

    public function registerCatchEvents(EngineInterface $engine);
}
