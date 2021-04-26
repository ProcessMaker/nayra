<?php

namespace ProcessMaker\Test\Models;

use Exception;
use ProcessMaker\Nayra\Bpmn\Collection;
use ProcessMaker\Nayra\Bpmn\Models\Token;
use ProcessMaker\Nayra\Contracts\Bpmn\ActivityInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\CatchEventInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\CollectionInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\EventBasedGatewayInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\GatewayInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\StartEventInterface;
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

    static $failNextPersistanceCall = false;

    /**
     * Sets to fail on next persistance call
     *
     * @return void
     */
    public static function failNextPersistanceCall()
    {
        static::$failNextPersistanceCall = true;
    }

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
    }

    /**
     * Persists instance and token data when a token arrives to an activity
     *
     * @param ActivityInterface $activity
     * @param TokenInterface $token
     *
     * @return mixed
     */
    public function persistActivityActivated(ActivityInterface $activity, TokenInterface $token)
    {
        $instanceRepository = $token->getInstance()->getProcess()->getRepository()->createExecutionInstanceRepository();
        $instanceRepository->persistInstanceUpdated($token->getInstance());
        if (static::$failNextPersistanceCall) {
            static::$failNextPersistanceCall = false;
            throw new Exception('Failure expected when activity persists');
        }
    }

    /**
     * Persists instance and token data when a token within an activity change to error state
     *
     * @param ActivityInterface $activity
     * @param TokenInterface $token
     *
     * @return mixed
     */
    public function persistActivityException(ActivityInterface $activity, TokenInterface $token)
    {
        $instanceRepository = $token->getInstance()->getProcess()->getRepository()->createExecutionInstanceRepository();
        $instanceRepository->persistInstanceUpdated($token->getInstance());
    }

    /**
     * Persists instance and token data when a token is completed within an activity
     *
     * @param ActivityInterface $activity
     * @param TokenInterface $token
     *
     * @return mixed
     */
    public function persistActivityCompleted(ActivityInterface $activity, TokenInterface $token)
    {
        $instanceRepository = $token->getInstance()->getProcess()->getRepository()->createExecutionInstanceRepository();
        $instanceRepository->persistInstanceUpdated($token->getInstance());
    }

    /**
     * Persists instance and token data when a token is closed by an activity
     *
     * @param ActivityInterface $activity
     * @param TokenInterface $token
     *
     * @return mixed
     */
    public function persistActivityClosed(ActivityInterface $activity, TokenInterface $token)
    {
        $instanceRepository = $token->getInstance()->getProcess()->getRepository()->createExecutionInstanceRepository();
        $instanceRepository->persistInstanceUpdated($token->getInstance());
    }

    /**
     * Get persist calls
     *
     * @return int
     */
    public function getPersistCalls()
    {
        return $this->persistCalls;
    }

    /**
     * Reset persist calls
     *
     */
    public function resetPersistCalls()
    {
        $this->persistCalls = 0;
    }

    /**
     * Persists instance and token data when a token arrives in a throw event
     *
     * @param ThrowEventInterface $event
     * @param TokenInterface $token
     *
     * @return mixed
     */
    public function persistThrowEventTokenArrives(ThrowEventInterface $event, TokenInterface $token)
    {
        $this->persistCalls++;
    }

    /**
     * Persists instance and token data when a token is consumed in a throw event
     *
     * @param ThrowEventInterface $endEvent
     * @param TokenInterface $token
     *
     * @return mixed
     */
    public function persistThrowEventTokenConsumed(ThrowEventInterface $endEvent, TokenInterface $token)
    {
        $this->persistCalls++;
    }

    /**
     * Persists instance and token data when a token is passed in a throw event
     *
     * @param ThrowEventInterface $endEvent
     * @param TokenInterface $token
     *
     * @return mixed
     */
    public function persistThrowEventTokenPassed(ThrowEventInterface $endEvent, TokenInterface $token)
    {
        $this->persistCalls++;
    }

    /**
     * Persists instance and token data when a token arrives in a gateway
     *
     * @param GatewayInterface $exclusiveGateway
     * @param TokenInterface $token
     *
     * @return mixed
     */
    public function persistGatewayTokenArrives(GatewayInterface $exclusiveGateway, TokenInterface $token)
    {
        $this->persistCalls++;
    }

    /**
     * Persists instance and token data when a token is consumed in a gateway
     *
     * @param GatewayInterface $exclusiveGateway
     * @param TokenInterface $token
     *
     * @return mixed
     */
    public function persistGatewayTokenConsumed(GatewayInterface $exclusiveGateway, TokenInterface $token)
    {
        $this->persistCalls++;
    }

    /**
     * Persists instance and token data when a token is passed in a gateway
     *
     * @param GatewayInterface $exclusiveGateway
     * @param TokenInterface $token
     *
     * @return mixed
     */
    public function persistGatewayTokenPassed(GatewayInterface $exclusiveGateway, TokenInterface $token)
    {
        $this->persistCalls++;
    }

    /**
     * Persists instance and token data when a token arrives in a catch event
     *
     * @param CatchEventInterface $intermediateCatchEvent
     * @param TokenInterface $token
     *
     * @return mixed
     */
    public function persistCatchEventTokenArrives(CatchEventInterface $intermediateCatchEvent, TokenInterface $token)
    {
        $this->persistCalls++;
    }

    /**
     * Persists instance and token data when a token is consumed in a catch event
     *
     * @param CatchEventInterface $intermediateCatchEvent
     * @param TokenInterface $token
     *
     * @return mixed
     */
    public function persistCatchEventTokenConsumed(CatchEventInterface $intermediateCatchEvent, TokenInterface $token)
    {
        $this->persistCalls++;
    }

    /**
     * Persists instance and token data when a token is passed in a catch event
     *
     * @param CatchEventInterface $intermediateCatchEvent
     * @param Collection $consumedTokens
     *
     * @return mixed
     */
    public function persistCatchEventTokenPassed(CatchEventInterface $intermediateCatchEvent, Collection $consumedTokens)
    {
        $this->persistCalls++;
    }

    /**
     * Persists instance and token data when a message arrives to a catch event
     *
     * @param CatchEventInterface $intermediateCatchEvent
     * @param TokenInterface $token
     */
    public function persistCatchEventMessageArrives(CatchEventInterface $intermediateCatchEvent, TokenInterface $token)
    {
        $this->persistCalls++;
    }

    /**
     * Persists instance and token data when a message is consumed in a catch event
     *
     * @param CatchEventInterface $intermediateCatchEvent
     * @param TokenInterface $token
     */
    public function persistCatchEventMessageConsumed(CatchEventInterface $intermediateCatchEvent, TokenInterface $token)
    {
        $this->persistCalls++;
    }

    /**
     * Persists tokens that triggered a Start Event
     *
     * @param StartEventInterface $startEvent
     * @param CollectionInterface $tokens
     *
     */
    public function persistStartEventTriggered(StartEventInterface $startEvent, CollectionInterface $tokens)
    {
    }

    /**
     * Persists instance and token data when a token is consumed in a event based gateway
     *
     * @param \ProcessMaker\Nayra\Contracts\Bpmn\EventBasedGatewayInterface $eventBasedGateway
     * @param \ProcessMaker\Nayra\Contracts\Bpmn\TokenInterface $passedToken
     * @param \ProcessMaker\Nayra\Contracts\Bpmn\CollectionInterface $consumedTokens
     *
     * @return mixed
     */
    public function persistEventBasedGatewayActivated(EventBasedGatewayInterface $eventBasedGateway, TokenInterface $passedToken, CollectionInterface $consumedTokens)
    {
    }
}
