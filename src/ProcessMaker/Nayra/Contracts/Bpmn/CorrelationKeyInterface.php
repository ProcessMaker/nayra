<?php

namespace ProcessMaker\Nayra\Contracts\Bpmn;

interface CorrelationKeyInterface
{

    /**
     * Get the CorrelationProperties representing the partial keys of
     * this CorrelationKey.
     *
     * @return CorrelationPropertyInterface[]
     */
    public function getCorrelationProperty();
}
