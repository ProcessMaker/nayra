<?php

namespace ProcessMaker\Nayra\Contracts\Repositories;

use ProcessMaker\Nayra\Bpmn\Collection;
use ProcessMaker\Nayra\Contracts\Bpmn\ActivityInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\CatchEventInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\CollectionInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\EventBasedGatewayInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\FlowInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\GatewayInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\StartEventInterface;
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
     * Persists instance and token data when a token arrives to an activity
     *
     * @param ActivityInterface $activity
     * @param TokenInterface $token
     * @param FlowInterface $sequenceFlow
     *
     * @return mixed
     */
    public function persistActivityActivated(ActivityInterface $activity, TokenInterface $token, FlowInterface $sequenceFlow);

    /**
     * Persists instance and token data when a token within an activity change to error state
     *
     * @param ActivityInterface $activity
     * @param TokenInterface $token
     *
     * @return mixed
     */
    public function persistActivityException(ActivityInterface $activity, TokenInterface $token);

    /**
     * Persists instance and token data when a token is completed within an activity
     *
     * @param ActivityInterface $activity
     * @param TokenInterface $token
     *
     * @return mixed
     */
    public function persistActivityCompleted(ActivityInterface $activity, TokenInterface $token);

    /**
     * Persists instance and token data when a token is closed by an activity
     *
     * @param ActivityInterface $activity
     * @param TokenInterface $token
     *
     * @return mixed
     */
    public function persistActivityClosed(ActivityInterface $activity, TokenInterface $token);

    /**
     * Persists instance and token data when a token arrives in a throw event
     *
     * @param ThrowEventInterface $event
     * @param TokenInterface $token
     *
     * @return mixed
     */
    public function persistThrowEventTokenArrives(ThrowEventInterface $event, TokenInterface $token);

    /**
     * Persists instance and token data when a token is consumed in a throw event
     *
     * @param ThrowEventInterface $endEvent
     * @param TokenInterface $token
     *
     * @return mixed
     */
    public function persistThrowEventTokenConsumed(ThrowEventInterface $endEvent, TokenInterface $token);

    /**
     * Persists instance and token data when a token is passed in a throw event
     *
     * @param ThrowEventInterface $endEvent
     * @param TokenInterface $token
     *
     * @return mixed
     */
    public function persistThrowEventTokenPassed(ThrowEventInterface $endEvent, TokenInterface $token);

    /**
     * Persists instance and token data when a token arrives in a gateway
     *
     * @param GatewayInterface $exclusiveGateway
     * @param TokenInterface $token
     *
     * @return mixed
     */
    public function persistGatewayTokenArrives(GatewayInterface $exclusiveGateway, TokenInterface $token);

    /**
     * Persists instance and token data when a token is consumed in a gateway
     *
     * @param GatewayInterface $exclusiveGateway
     * @param TokenInterface $token
     *
     * @return mixed
     */
    public function persistGatewayTokenConsumed(GatewayInterface $exclusiveGateway, TokenInterface $token);

    /**
     * Persists instance and token data when a token is passed in a gateway
     *
     * @param GatewayInterface $exclusiveGateway
     * @param TokenInterface $token
     *
     * @return mixed
     */
    public function persistGatewayTokenPassed(GatewayInterface $exclusiveGateway, TokenInterface $token);

    /**
     * Persists instance and token data when a token arrives in a catch event
     *
     * @param CatchEventInterface $intermediateCatchEvent
     * @param TokenInterface $token
     *
     * @return mixed
     */
    public function persistCatchEventTokenArrives(CatchEventInterface $intermediateCatchEvent, TokenInterface $token);

    /**
     * Persists instance and token data when a token is consumed in a catch event
     *
     * @param CatchEventInterface $intermediateCatchEvent
     * @param TokenInterface $token
     *
     * @return mixed
     */
    public function persistCatchEventTokenConsumed(CatchEventInterface $intermediateCatchEvent, TokenInterface $token);

    /**
     * Persists instance and token data when a token is passed in a catch event
     *
     * @param CatchEventInterface $intermediateCatchEvent
     * @param Collection $consumedTokens
     *
     * @return mixed
     */
    public function persistCatchEventTokenPassed(CatchEventInterface $intermediateCatchEvent, Collection $consumedTokens);

    /**
     * Persists instance and token data when a message arrives to a catch event
     *
     * @param CatchEventInterface $intermediateCatchEvent
     * @param TokenInterface $token
     */
    public function persistCatchEventMessageArrives(CatchEventInterface $intermediateCatchEvent, TokenInterface $token);

    /**
     * Persists instance and token data when a message is consumed in a catch event
     *
     * @param CatchEventInterface $intermediateCatchEvent
     * @param TokenInterface $token
     */
    public function persistCatchEventMessageConsumed(CatchEventInterface $intermediateCatchEvent, TokenInterface $token);

    /**
     * Persists tokens that triggered a Start Event
     *
     * @param \ProcessMaker\Nayra\Contracts\Bpmn\StartEventInterface $startEvent
     * @param \ProcessMaker\Nayra\Contracts\Bpmn\CollectionInterface $tokens
     *
     * @return mixed
     */
    public function persistStartEventTriggered(StartEventInterface $startEvent, CollectionInterface $tokens);

    /**
     * Persists instance and token data when a token is consumed in a event based gateway
     *
     * @param EventBasedGatewayInterface $eventBasedGateway
     * @param TokenInterface $passedToken
     * @param CollectionInterface $consumedTokens
     *
     * @return mixed
     */
    public function persistEventBasedGatewayActivated(EventBasedGatewayInterface $eventBasedGateway, TokenInterface $passedToken, CollectionInterface $consumedTokens);
}
