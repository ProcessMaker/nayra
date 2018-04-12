<?php

namespace ProcessMaker\Nayra\Contracts\Bpmn;

/**
 *
 */
interface ParticipantInterface extends EntityInterface
{

    /**
     * @return ProcessInterface
     */
    public function getProcess();
}
