<?php

namespace ProcessMaker\Nayra\Bpmn;

use ProcessMaker\Nayra\Contracts\Bpmn\StateInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\TokenInterface;
use ProcessMaker\Nayra\Contracts\Engine\ExecutionInstanceInterface;

/**
 * Trait for a token.
 *
 * @package ProcessMaker\Nayra\Bpmn
 */
trait TokenTrait
{
    use BaseTrait;

    /**
     *
     * @var StateInterface
     */
    private $owner;

    /**
     * @var \ProcessMaker\Nayra\Contracts\Engine\ExecutionInstanceInterface $instance
     */
    private $instance;

    /**
     * Get the owner of the token.
     *
     * @return \ProcessMaker\Nayra\Contracts\Bpmn\StateInterface
     */
    public function getOwner()
    {
        return $this->owner;
    }

    /**
     * Get the owner of the token.
     *
     * @param \ProcessMaker\Nayra\Contracts\Bpmn\StateInterface $owner
     *
     * @return StateInterface
     */
    public function setOwner(StateInterface $owner)
    {
        $this->owner = $owner;
        return $this;
    }

    /**
     * Get owner status for the current token.
     *
     * @return string
     */
    public function getOwnerStatus()
    {
        return $this->getOwner()->getName();
    }

    /**
     * Set the owner execution instance of the token.
     *
     * @param ExecutionInstanceInterface|null $instance
     *
     * @return $this
     */
    public function setInstance(ExecutionInstanceInterface $instance = null)
    {
        $this->instance = $instance;
        return $this;
    }

    /**
     * Get the owner execution instance of the token.
     *
     * @return ExecutionInstanceInterface $instance
     */
    public function getInstance()
    {
        return $this->instance;
    }

    /**
     * Get token internal status.
     *
     * @return string
     */
    public function getStatus()
    {
        return $this->getProperty(TokenInterface::BPMN_PROPERTY_STATUS);
    }

    /**
     * Set token internal status.
     *
     * @param string $status
     *
     * @return $this
     */
    public function setStatus($status)
    {
        $this->setProperty(TokenInterface::BPMN_PROPERTY_STATUS, $status);
        return $this;
    }

    /**
     * Get the owner element of the token.
     *
     * @return \ProcessMaker\Nayra\Contracts\Bpmn\FlowNodeInterface
     */
    public function getOwnerElement()
    {
        return $this->getOwner()->getOwner();
    }
}
