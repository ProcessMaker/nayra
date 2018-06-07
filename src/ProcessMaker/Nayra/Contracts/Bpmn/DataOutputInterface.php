<?php

namespace ProcessMaker\Nayra\Contracts\Bpmn;

/**
 * DataOutput interface.
 *
 * @package ProcessMaker\Nayra\Contracts\Bpmn
 */
interface DataOutputInterface extends ItemAwareElementInterface
{
    const BPMN_PROPERTY_ITEM_SUBJECT = 'itemSubject';
    const BPMN_PROPERTY_ITEM_SUBJECT_REF = 'itemSubjectRef';
    const BPMN_PROPERTY_IS_COLLECTION = 'isCollection';

    /**
     * 
     *
     * @return boolean
     */
    public function isCollection();
}
