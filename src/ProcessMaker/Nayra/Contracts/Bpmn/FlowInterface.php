<?php

namespace ProcessMaker\Nayra\Contracts\Bpmn;

/**
 * FlowInterface
 */
interface FlowInterface extends EntityInterface
{
    /**
     * Type of element.
     */
    const TYPE = 'bpmnFlow';

    const BPMN_PROPERTY_CONDITION_EXPRESSION = 'conditionExpression';

    const BPMN_PROPERTY_IS_DEFAULT = 'isDefault';

    const BPMN_PROPERTY_SOURCE = 'source';

    const BPMN_PROPERTY_TARGET = 'target';

    const BPMN_PROPERTY_SOURCE_REF = 'sourceRef';

    const BPMN_PROPERTY_TARGET_REF = 'targetRef';

    const TYPE_DEFAULT = 'DEFAULT';

    const TYPE_SEQUENCE = 'SEQUENCE';

    const TYPE_MESSAGE = 'MESSAGE';

    /**
     * Properties.
     */
    const PROPERTIES = [
        'FLO_UID' => '',
        'DIA_UID' => '',
        'FLO_TYPE' => 'SEQUENCE',
        'FLO_NAME' => '',
        'FLO_ELEMENT_ORIGIN' => '',
        'FLO_ELEMENT_ORIGIN_TYPE' => '',
        'FLO_ELEMENT_ORIGIN_PORT' => '0',
        'FLO_ELEMENT_DEST' => '',
        'FLO_ELEMENT_DEST_TYPE' => '',
        'FLO_ELEMENT_DEST_PORT' => '0',
        'FLO_IS_INMEDIATE' => null,
        'FLO_CONDITION' => null,
        'FLO_X1' => '0',
        'FLO_Y1' => '0',
        'FLO_X2' => '0',
        'FLO_Y2' => '0',
        'FLO_STATE' => null,
        'FLO_POSITION' => '0',
    ];

    /**
     * Child elements.
     */
    const ELEMENTS = [

    ];

    /**
     * Get Process of the flow.
     *
     * @return ProcessInterface
     */
    public function getProcess();

    /**
     * Get Process of the flow.
     *
     * @return ProcessInterface
     */
    public function setProcess(ProcessInterface $process);

    /**
     * @return FlowNodeInterface
     */
    public function getSource();

    /**
     * @param FlowNodeInterface $source
     *
     * @return $this
     */
    public function setSource(FlowNodeInterface $source);

    /**
     * @return FlowNodeInterface
     */
    public function getTarget();

    /**
     * @param FlowNodeInterface $target
     *
     * @return $this
     */
    public function setTarget(FlowNodeInterface $target);

    /**
     * @return bool
     */
    public function hasCondition();

    /**
     * @return callable
     */
    public function getCondition();

    /**
     * @return bool
     */
    public function isDefault();
}
