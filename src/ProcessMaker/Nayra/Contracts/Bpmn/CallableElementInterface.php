<?php

namespace ProcessMaker\Nayra\Contracts\Bpmn;

use ProcessMaker\Nayra\Contracts\Engine\EngineInterface;
use ProcessMaker\Nayra\Contracts\Engine\ExecutionInstanceInterface;

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
     * @param EngineInterface|null $engine
     *
     * @return EngineInterface
     */
    public function setEngine(EngineInterface $engine = null);

    /**
     * Get the engine that controls the elements.
     *
     * @return EngineInterface
     */
    public function getEngine();

    /**
     * Call and create an instance of the callable element.
     *
     * @return ExecutionInstanceInterface
     */
    public function call();
}
