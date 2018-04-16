<?php

namespace ProcessMaker\Nayra\Contracts\Bpmn;

/**
 * DataInput interface.
 *
 * @package ProcessMaker\Nayra\Contracts\Bpmn
 */
interface DataInputInterface extends ItemAwareElementInterface
{

    /**
     * Get true if the DataInput represents a collection of elements.
     *
     * @return boolean
     */
    public function isCollection();
}
