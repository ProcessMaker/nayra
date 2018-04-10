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

    /**
     * Get owner status for the current token.
     *
     * @return string
     */
    public function getOwnerStatus();

    /**
     * Get token internal status.
     *
     * @return string
     */
    public function getStatus();

    /**
     * Set token internal status.
     *
     * @param string $status
     *
     * @return $this
     */
    public function setStatus($status);
}
