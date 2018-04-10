<?php

namespace ProcessMaker\Nayra\Bpmn;

use ProcessMaker\Nayra\Contracts\Bpmn\StateInterface;

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

    public function initToken(StateInterface $owner)
    {
        $this->owner = $owner;
    }

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
}
