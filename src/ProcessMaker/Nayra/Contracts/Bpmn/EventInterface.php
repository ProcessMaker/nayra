<?php

namespace ProcessMaker\Nayra\Contracts\Bpmn;

use ProcessMaker\Nayra\Contracts\Bpmn\FlowNodeInterface;

/**
 * EventInterface
 *
 */
interface EventInterface extends FlowNodeInterface
{

    /**
     * Type of element.
     */
    const TYPE = 'bpmnEvent';

    const TYPE_START = 'START';
    const TYPE_INTERMEDIATE = 'INTERMEDIATE';
    const TYPE_END = 'END';
    const MARKER_EMPTY = 'EMPTY';
    const MARKER_MESSAGETHROW = 'MESSAGETHROW';
    const MARKER_EMAIL = 'EMAIL';
    const MARKER_MESSAGECATCH = 'MESSAGECATCH';
    const BEHAVIOR_THROW = 'THROW';
    const BEHAVIOR_CATCH = 'CATCH';

    /**
     * Events
     */

    const EVENT_EVENT_TRIGGERED = 'EventTriggered';

    /**
     * Token states defined for Event
     */
    const TOKEN_STATE_ACTIVE = 'ACTIVE';

    /**
     * Properties.
     */
    const PROPERTIES = [
        'EVN_UID' => '',
        'EVN_NAME' => NULL,
        'EVN_TYPE' => 'END',
        'EVN_MARKER' => 'EMPTY',
        'EVN_IS_INTERRUPTING' => '1',
        'EVN_ATTACHED_TO' => '',
        'EVN_CANCEL_ACTIVITY' => '0',
        'EVN_ACTIVITY_REF' => '',
        'EVN_WAIT_FOR_COMPLETION' => '1',
        'EVN_ERROR_NAME' => NULL,
        'EVN_ERROR_CODE' => NULL,
        'EVN_ESCALATION_NAME' => NULL,
        'EVN_ESCALATION_CODE' => NULL,
        'EVN_CONDITION' => NULL,
        'EVN_MESSAGE' => NULL,
        'EVN_OPERATION_NAME' => NULL,
        'EVN_OPERATION_IMPLEMENTATION_REF' => NULL,
        'EVN_TIME_DATE' => NULL,
        'EVN_TIME_CYCLE' => NULL,
        'EVN_TIME_DURATION' => NULL,
        'EVN_BEHAVIOR' => 'CATCH'
    ];

    /**
     * Child elements.
     */
    const ELEMENTS = [

    ];

    
    /**
     * Get Process of the event.
     *
     * @return ProcessInterface
     */
    public function getProcess();

    /**
     * Get Process of the event.
     *
     * @return ProcessInterface
     */
    public function setProcess(ProcessInterface $process);
    

}
