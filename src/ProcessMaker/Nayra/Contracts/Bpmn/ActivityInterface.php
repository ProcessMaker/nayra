<?php

namespace ProcessMaker\Nayra\Contracts\Bpmn;

use ProcessMaker\Nayra\Contracts\Bpmn\FlowNodeInterface;

/**
 * Activity interface.
 *
 * @package ProcessMaker\Nayra\Contracts\Bpmn
 */
interface ActivityInterface extends EntityInterface, FlowNodeInterface
{

    /**
     * Type of element.
     */
    const TYPE = 'bpmnActivity';

    const TYPE_TASK = 'TASK';
    const TYPE_SUB_PROCESS = 'SUB_PROCESS';
    const TASK_TYPE_EMPTY = 'EMPTY';
    const TASK_TYPE_COLLAPSED = 'COLLAPSED';
    const LOOP_TYPE_NONE = 'NONE';
    const LOOP_TYPE_PARALLEL = 'PARALLEL';
    const LOOP_TYPE_EMPTY = 'EMPTY';

    /**
     * Events defined for Activity
     */
    const EVENT_ACTIVITY_ACTIVATED = 'ActivityActivated';
    const EVENT_ACTIVITY_COMPLETED = 'ActivityCompleted';
    const EVENT_ACTIVITY_EXCEPTION = 'ActivityException';
    const EVENT_ACTIVITY_CLOSED = 'ActivityClosed';

    /**
     * Token states defined for Activity
     */
    const TOKEN_STATE_ACTIVE = 'ACTIVE';
    const TOKEN_STATE_FAILING = 'FAILING';
    const TOKEN_STATE_COMPLETED = 'COMPLETED';

    /**
     * Properties.
     */
    const PROPERTIES = [
        'ACT_UID' => '',
        'PRO_ID' => '',
        'ACT_NAME' => NULL,
        'ACT_TYPE' => 'TASK',
        'ACT_IS_FOR_COMPENSATION' => '0',
        'ACT_START_QUANTITY' => '1',
        'ACT_COMPLETION_QUANTITY' => '1',
        'ACT_TASK_TYPE' => 'EMPTY',
        'ACT_IMPLEMENTATION' => NULL,
        'ACT_INSTANTIATE' => '0',
        'ACT_SCRIPT_TYPE' => NULL,
        'ACT_SCRIPT' => NULL,
        'ACT_LOOP_TYPE' => 'NONE',
        'ACT_TEST_BEFORE' => '0',
        'ACT_LOOP_MAXIMUM' => '0',
        'ACT_LOOP_CONDITION' => NULL,
        'ACT_LOOP_CARDINALITY' => '0',
        'ACT_LOOP_BEHAVIOR' => 'NONE',
        'ACT_IS_ADHOC' => '0',
        'ACT_IS_COLLAPSED' => '1',
        'ACT_COMPLETION_CONDITION' => NULL,
        'ACT_ORDERING' => 'PARALLEL',
        'ACT_CANCEL_REMAINING_INSTANCES' => '1',
        'ACT_PROTOCOL' => NULL,
        'ACT_METHOD' => NULL,
        'ACT_IS_GLOBAL' => '0',
        'ACT_REFERER' => '',
        'ACT_DEFAULT_FLOW' => '',
        'ACT_MASTER_DIAGRAM' => ''
    ];

    /**
     * Child elements.
     */
    const ELEMENTS = [

    ];

    
    /**
     * Get Process of the activity.
     *
     * @return ProcessInterface
     */
    public function getProcess();

    /**
     * Get Process of the activity.
     *
     * @param ProcessInterface $process
     *
     * @return ProcessInterface
     */
    public function setProcess(ProcessInterface $process);

    /**
     * Complete the activity instance identified by the token.
     *
     * @param TokenInterface $token
     *
     * @return $this
     */
    public function complete(TokenInterface $token);

    /**
     * Get tokens in the activity.
     *
     * @return CollectionInterface
     */
    public function getTokens();
}
