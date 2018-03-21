<?php

namespace ProcessMaker\Nayra\Bpmn;

use ProcessMaker\Nayra\Contracts\Bpmn\TokenInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\StateInterface;
use ProcessMaker\Nayra\Contracts\Engine\EngineInterface;

/**
 * Trait for a token.
 *
 * @package ProcessMaker\Nayra\Bpmn
 */
trait TokenTrait
{
    use EntityTrait;

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
     * @return StateInterface
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
}
