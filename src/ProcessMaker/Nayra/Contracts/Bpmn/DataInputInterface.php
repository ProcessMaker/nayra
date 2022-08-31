<?php

namespace ProcessMaker\Nayra\Contracts\Bpmn;

/**
 * DataInput interface.
 */
interface DataInputInterface extends ItemAwareElementInterface
{
    const BPMN_PROPERTY_ITEM_SUBJECT = 'itemSubject';

    const BPMN_PROPERTY_ITEM_SUBJECT_REF = 'itemSubjectRef';

    const BPMN_PROPERTY_IS_COLLECTION = 'isCollection';

    /**
     * Get true if the DataInput represents a collection of elements.
     *
     * @return bool
     */
    public function isCollection();
}
