<?php

namespace ProcessMaker\Nayra\Contracts\Repositories;

use ProcessMaker\Nayra\Bpmn\Collection;
use ProcessMaker\Nayra\Contracts\Bpmn\CatchEventInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\GatewayInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\ThrowEventInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\TokenInterface;

/**
 * Repository for TokenInterface
 *
 */
interface TokenRepositoryInterface
{

    /**
     * Create a token instance.
     *
     * @return TokenInterface
     */
    public function createTokenInstance();

    /**
     * Load a token from a persistent storage.
     *
     * @param string $uid
     *
     * @return \ProcessMaker\Nayra\Contracts\Bpmn\TokenInterface
     */
    public function loadTokenByUid($uid);

    /**
     * Create or update a activity to a persistent storage.
     *
     * @param \ProcessMaker\Nayra\Contracts\Bpmn\TokenInterface $token
     * @param bool $saveChildElements
     *
     * @return $this
     */
    public function store(TokenInterface $token, $saveChildElements = false);

    /**
     * Persists instance and token data when a token arrives
     *
     * @param ThrowEventInterface $event
     * @param Collection $tokens
     *
     * @return mixed
     */
    public function persistThrowEventTokenArrives(ThrowEventInterface $event, Collection $tokens);

    /**
     * Persists instance and token data when a token arrives
     *
     * @param ThrowEventInterface $endEvent
     * @param Collection $tokens
     *
     * @return mixed
     */
    public function persistThrowEventTokenConsumed(ThrowEventInterface $endEvent, Collection $tokens);


    /**
     * Persists instance and token data when a token arrives
     *
     * @param ThrowEventInterface $endEvent
     * @param Collection $tokens
     *
     * @return mixed
     */
    public function persistThrowEventTokenPassed(ThrowEventInterface $endEvent, Collection $tokens);

    /**
     * Persists instance and token data when a token arrives
     *
     * @param GatewayInterface $exclusiveGateway
     * @param Collection $tokens
     *
     * @return mixed
     */
    public function persistGatewayTokenArrives(GatewayInterface $exclusiveGateway, Collection $tokens);

    /**
     * Persists instance and token data when a token arrives
     *
     * @param GatewayInterface $exclusiveGateway
     * @param Collection $tokens
     *
     * @return mixed
     */
    public function persistGatewayTokenConsumed(GatewayInterface $exclusiveGateway, Collection $tokens);

    /**
     * Persists instance and token data when a token arrives
     *
     * @param GatewayInterface $exclusiveGateway
     * @param Collection $tokens
     *
     * @return mixed
     */
    public function persistGatewayTokenPassed(GatewayInterface $exclusiveGateway, Collection $tokens);

    /**
     * Persists instance and token data when a token arrives
     *
     * @param CatchEventInterface $intermediateCatchEvent
     * @param Collection $tokens
     *
     * @return mixed
     */
    public function persistCatchEventTokenArrives(CatchEventInterface $intermediateCatchEvent, Collection $tokens);

    /**
     * Persists instance and token data when a token arrives
     *
     * @param CatchEventInterface $intermediateCatchEvent
     * @param Collection $tokens
     *
     * @return mixed
     */
    public function persistCatchEventTokenConsumed(CatchEventInterface $intermediateCatchEvent, Collection $tokens);

    /**
     * Persists instance and token data when a token arrives
     *
     * @param CatchEventInterface $intermediateCatchEvent
     * @param Collection $tokens
     *
     * @return mixed
     */
    public function persistCatchEventTokenPassed(CatchEventInterface $intermediateCatchEvent, Collection $tokens);
}
