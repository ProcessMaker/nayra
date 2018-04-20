<?php

namespace ProcessMaker\Nayra\Contracts\Bpmn;

use ProcessMaker\Nayra\Contracts\Engine\EngineInterface;

/**
 * CallableElement interface.
 *
 * @package ProcessMaker\Nayra\Contracts\Bpmn
 */
interface CallableElementInterface extends EntityInterface
{

    /**
     * Set the engine that controls the elements.
     *
     * @param EngineInterface $engine
     *
     * @return EngineInterface
     */
    //public function setEngine(EngineInterface $engine);

    /**
     * Get the engine that controls the elements.
     *
     * @return EngineInterface
     */
    //public function getEngine();
}
