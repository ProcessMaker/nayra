<?php

namespace ProcessMaker\Nayra\Contracts\Bpmn;


/**
 * Token of a process instance.
 *
 * @package ProcessMaker\Nayra\Contracts\Bpmn
 */
interface TokenInterface extends EntityInterface
{

    /**
     * Get the owner of the token.
     *
     * @return StateInterface
     */
    public function getOwner();

    /**
     * Set the owner of the token.
     *
     * @param StateInterface $owner
     *
     * @return mixed
     */
    public function setOwner(StateInterface $owner);
}
