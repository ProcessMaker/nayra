<?php

namespace ProcessMaker\Nayra\Contracts\Bpmn;

use ProcessMaker\Nayra\Contracts\Engine\ExecutionInstanceInterface;

/**
 * Token of a process instance.
 *
 * @package ProcessMaker\Nayra\Contracts\Bpmn
 */
interface TokenInterface extends EntityInterface
{

    const BPMN_PROPERTY_MESSAGE = 'message';
    const BPMN_PROPERTY_STATUS = 'status';

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

    /**
     * Set the owner execution instance of the token.
     *
     * @param ExecutionInstanceInterface|null $instance
     *
     * @return $this
     */
    public function setInstance(ExecutionInstanceInterface $instance = null);

    /**
     * Get the owner execution instance of the token.
     *
     * @return ExecutionInstanceInterface $instance
     */
    public function getInstance();
}
