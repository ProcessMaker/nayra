<?php

namespace ProcessMaker\Test\Models;

use ProcessMaker\Nayra\Bpmn\Collection;
use ProcessMaker\Nayra\Contracts\Bpmn\CatchEventInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\GatewayInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\ThrowEventInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\TokenInterface;
use ProcessMaker\Nayra\Contracts\Repositories\TokenRepositoryInterface;

/**
 * Token Repository.
 *
 * @package ProcessMaker\Models
 */
class TokenRepository implements TokenRepositoryInterface
{
    public $persistCalls = 0;

    /**
     * Create a token instance.
     *
     * @return TokenInterface
     */
    public function createTokenInstance()
    {
        $token = new Token();
        return $token;
    }

    /**
     * Load a token from a persistent storage.
     *
     * @param string $uid
     *
     * @return \ProcessMaker\Nayra\Contracts\Bpmn\TokenInterface
     */
    public function loadTokenByUid($uid)
    {
        // TODO: Implement loadTokenByUid() method.
    }

    /**
     * Create or update a activity to a persistent storage.
     *
     * @param \ProcessMaker\Nayra\Contracts\Bpmn\TokenInterface $token
     * @param bool $saveChildElements
     *
     * @return $this
     */
    public function store(TokenInterface $token, $saveChildElements = false)
    {
        // TODO: Implement store() method.
    }

    /**
     * @param ThrowEventInterface $event
     * @param TokenInterface $token
     * @return mixed
     */
    public function persistThrowEventTokenArrives(ThrowEventInterface $event, Collection $tokens)
    {
        $this->persistCalls++;
    }

    /**
     * @param ThrowEventInterface $endEvent
     * @param TokenInterface $token
     * @return mixed
     */
    public function persistThrowEventTokenConsumed(ThrowEventInterface $endEvent, Collection $tokens)
    {
        $this->persistCalls++;
    }

    /**
     * @param ThrowEventInterface $endEvent
     * @param TokenInterface $token
     * @return mixed
     */
    public function persistThrowEventTokenPassed(ThrowEventInterface $endEvent, Collection $tokens)
    {
        $this->persistCalls++;
    }

    /**
     * @param GatewayInterface $exclusiveGateway
     * @param TokenInterface $token
     * @return mixed
     */
    public function persistGatewayTokenArrives(GatewayInterface $exclusiveGateway, Collection $tokens)
    {
        $this->persistCalls++;
    }

    /**
     * @param GatewayInterface $exclusiveGateway
     * @param TokenInterface $token
     * @return mixed
     */
    public function persistGatewayTokenConsumed(GatewayInterface $exclusiveGateway, Collection $tokens)
    {
        $this->persistCalls++;
    }

    /**
     * @param GatewayInterface $exclusiveGateway
     * @return mixed
     */
    public function persistGatewayTokenPassed(GatewayInterface $exclusiveGateway, Collection $tokens)
    {
        $this->persistCalls++;
    }

    /**
     * @param CatchEventInterface $intermediateCatchEvent
     * @param TokenInterface $token
     * @return mixed
     */
    public function persistCatchEventTokenArrives(CatchEventInterface $intermediateCatchEvent, Collection $tokens)
    {
        $this->persistCalls++;
    }

    /**
     * @param CatchEventInterface $intermediateCatchEvent
     * @param TokenInterface $token
     * @return mixed
     */
    public function persistCatchEventTokenConsumed(CatchEventInterface $intermediateCatchEvent, Collection $tokens)
    {
        $this->persistCalls++;
    }

    /**
     * @param CatchEventInterface $intermediateCatchEvent
     * @return mixed
     */
    public function persistCatchEventTokenPassed(CatchEventInterface $intermediateCatchEvent, Collection $tokens)
    {
        $this->persistCalls++;
    }

    public function getPersistCalls()
    {
        return $this->persistCalls;
    }

    public function resetPersistCalls()
    {
        $this->persistCalls = 0;
    }
}
