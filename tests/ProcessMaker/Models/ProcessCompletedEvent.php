<?php

namespace ProcessMaker\Models;

use ProcessMaker\Nayra\Contracts\Bpmn\ProcessInterface;

/**
 * Event raised when a process is completed.
 *
 * @package ProcessMaker\Models
 */
class ProcessCompletedEvent
{

    /**
     * ProcessCompletedEvent constructor.
     *
     * @param ProcessInterface $process
     * @param array $params
     */
    public function __construct(ProcessInterface $process, $params = [])
    {
        $this->process = $process;
        $this->params = $params;
    }
}
