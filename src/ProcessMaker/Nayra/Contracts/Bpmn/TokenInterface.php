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
    const BPMN_PROPERTY_INDEX = 'index';

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
     * Get token internal index.
     *
     * @return index
     */
    public function getIndex();

    /**
     * Set token internal index.
     *
     * @param int $index
     *
     * @return $this
     */
    public function setIndex($index);

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

    /**
     * Get the owner element of the token.
     *
     * @return \ProcessMaker\Nayra\Contracts\Bpmn\FlowNodeInterface
     */
    public function getOwnerElement();
}
