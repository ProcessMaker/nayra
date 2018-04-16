<?php

namespace ProcessMaker\Nayra\Contracts\Bpmn;

/**
 * DataOutput interface.
 *
 * @package ProcessMaker\Nayra\Contracts\Bpmn
 */
interface DataOutputInterface extends ItemAwareElementInterface
{

    /**
     * 
     *
     * @return boolean
     */
    public function getIsCollection();
}
