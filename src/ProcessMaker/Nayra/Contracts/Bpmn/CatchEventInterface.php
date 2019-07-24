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

    const EVENT_CATCH_TOKEN_ARRIVES = 'CatchEventTokenArrives';

    /**
     * Get EventDefinitions that are triggers expected for a catch Event.
     *
     * @return EventDefinitionInterface[]
     */
    public function getEventDefinitions();

    /**
     * Register catch events.
     *
     * @param EngineInterface $engine
     *
     * @return $this
     */
    public function registerCatchEvents(EngineInterface $engine);

    /**
     * Register the BPMN elements with the engine.
     *
     * @param EngineInterface $engine
     *
     * @return FlowElementInterface
     */
    public function registerWithEngine(EngineInterface $engine);
}
