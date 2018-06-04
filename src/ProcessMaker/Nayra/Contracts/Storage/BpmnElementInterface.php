<?php

namespace ProcessMaker\Nayra\Contracts\Storage;

/**
 * BPMN Element interface
 *
 * @package \ProcessMaker\Nayra\Contracts\Storage
 */
interface BpmnElementInterface
{

    /**
     * Get instance of the BPMN element.
     *
     * @return \ProcessMaker\Nayra\Contracts\Bpmn\EntityInterface
     */
    public function getBpmnElementInstance();
}
