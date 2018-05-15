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
