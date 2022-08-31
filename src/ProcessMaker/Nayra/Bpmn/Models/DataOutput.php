<?php

namespace ProcessMaker\Nayra\Bpmn\Models;

use ProcessMaker\Nayra\Bpmn\FlowElementTrait;
use ProcessMaker\Nayra\Contracts\Bpmn\DataOutputInterface;

/**
 * Data output implementation.
 */
class DataOutput implements DataOutputInterface
{
    use FlowElementTrait;

    /**
     * Get the item subject.
     *
     * @return mixed
     */
    public function getItemSubject()
    {
        return $this->getProperty(static::BPMN_PROPERTY_ITEM_SUBJECT);
    }

    /**
     * Get true is the data input is a collection.
     *
     * @return bool
     */
    public function isCollection()
    {
        return $this->getProperty(static::BPMN_PROPERTY_IS_COLLECTION);
    }
}
