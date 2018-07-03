<?php

namespace ProcessMaker\Test\Models;

use ProcessMaker\Models\Token;
use ProcessMaker\Nayra\Bpmn\Collection;
use ProcessMaker\Nayra\Bpmn\RepositoryTrait;
use ProcessMaker\Nayra\Contracts\Bpmn\EndEventInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\ErrorInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\ExclusiveGatewayInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\IntermediateCatchEventInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\MessageInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\ScriptTaskInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\SignalInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\TokenInterface;
use ProcessMaker\Nayra\Contracts\Repositories\TokenRepositoryInterface;

/**
 * Token Repository.
 *
 * @package ProcessMaker\Models
 */
class TokenRepository implements TokenRepositoryInterface
{
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
     * @param EndEventInterface $event
     * @param TokenInterface $token
     *
     * @return mixed
     */
    public function persistThrowEventTokenArrives(EndEventInterface $event, TokenInterface $token)
    {
        // TODO: Implement persistThrowEventTokenArrives() method.
    }

    /**
     * @param EndEventInterface $endEvent
     * @param TokenInterface $token
     *
     * @return mixed
     */
    public function persistThrowEventTokenConsumed(EndEventInterface $endEvent, TokenInterface $token)
    {
        // TODO: Implement persistThrowEventTokenConsumed() method.
    }

    /**
     * @param ScriptTaskInterface $scriptTask
     * @param TokenInterface $token
     *
     * @return mixed
     */
    public function persistScriptTaskActivated(ScriptTaskInterface $scriptTask, TokenInterface $token)
    {
        // TODO: Implement persistScriptTaskActivated() method.
    }

    /**
     * @param EndEventInterface $endEvent
     * @param TokenInterface $token
     * @param ErrorInterface $error
     *
     *
     * @return mixed
     */
    public function persistThrowErrorEvent(EndEventInterface $endEvent, TokenInterface $token, ErrorInterface $error)
    {
        // TODO: Implement persistThrowErrorEvent() method.
    }

    /**
     * @param ExclusiveGatewayInterface $exclusiveGateway
     * @param TokenInterface $token
     *
     * @return mixed
     */
    public function persistGatewayTokenArrives(ExclusiveGatewayInterface $exclusiveGateway, TokenInterface $token)
    {
        // TODO: Implement persistGatewayTokenArrives() method.
    }

    /**
     * @param ExclusiveGatewayInterface $exclusiveGateway
     *
     * @return mixed
     */
    public function persistGatewayActivated(ExclusiveGatewayInterface $exclusiveGateway)
    {
        // TODO: Implement persistGatewayActivated() method.
    }

    /**
     * @param ExclusiveGatewayInterface $exclusiveGateway
     * @param TokenInterface $token
     *
     * @return mixed
     */
    public function persistGatewayTokenConsumed(ExclusiveGatewayInterface $exclusiveGateway, TokenInterface $token)
    {
        // TODO: Implement persistGatewayTokenConsumed() method.
    }

    /**
     * @param ExclusiveGatewayInterface $exclusiveGateway
     *
     * @return mixed
     */
    public function persistGatewayTokenPassed(ExclusiveGatewayInterface $exclusiveGateway)
    {
        // TODO: Implement persistGatewayTokenPassed() method.
    }

    /**
     * @param IntermediateCatchEventInterface $intermediateCatchEvent
     * @param TokenInterface $token
     *
     * @return mixed
     */
    public function persistCatchEventTokenArrives(IntermediateCatchEventInterface $intermediateCatchEvent, TokenInterface $token)
    {
        // TODO: Implement persistCatchEventTokenArrives() method.
    }

    /**
     * @param IntermediateCatchEventInterface $intermediateCatchEvent
     * @param TokenInterface $token
     *
     * @return mixed
     */
    public function persistCatchEventTokenCatch(IntermediateCatchEventInterface $intermediateCatchEvent, TokenInterface $token)
    {
        // TODO: Implement persistCatchEventTokenCatch() method.
    }

    /**
     * @param IntermediateCatchEventInterface $intermediateCatchEvent
     * @param TokenInterface $token
     *
     * @return mixed
     */
    public function persistCatchEventTokenConsumed(IntermediateCatchEventInterface $intermediateCatchEvent, TokenInterface $token)
    {
        // TODO: Implement persistCatchEventTokenConsumed() method.
    }

    /**
     * @param IntermediateCatchEventInterface $intermediateCatchEvent
     *
     * @return mixed
     */
    public function persistCatchEventTokenPassed(IntermediateCatchEventInterface $intermediateCatchEvent)
    {
        // TODO: Implement persistCatchEventTokenPassed() method.
    }

    /**
     * @param IntermediateCatchEventInterface $intermediateThrowEvent
     *
     * @return mixed
     */
    public function persistThrowEventTokenPassed(IntermediateCatchEventInterface $intermediateThrowEvent)
    {
        // TODO: Implement persistThrowEventTokenPassed() method.
    }

    /**
     * @param EndEventInterface $endEvent
     * @param TokenInterface $token
     * @param MessageInterface $message
     *
     * @return mixed
     */
    public function persistThrowMessageEvent(EndEventInterface $endEvent, TokenInterface $token, MessageInterface $message)
    {
        // TODO: Implement persistThrowMessageEvent() method.
    }

    /**
     * @param EndEventInterface $endEvent
     * @param TokenInterface $token
     * @param SignalInterface $message
     *
     * @return mixed
     */
    public function persistThrowSignalEvent(EndEventInterface $endEvent, TokenInterface $token, SignalInterface $message)
    {
        // TODO: Implement persistThrowSignalEvent() method.
    }

    /**
     * @param EndEventInterface $endEvent
     * @param TokenInterface $token
     * @param $param
     *
     * @return mixed
     */
    public function persistThrowTerminateEvent(EndEventInterface $endEvent, TokenInterface $token, $param)
    {
        // TODO: Implement persistThrowTerminateEvent() method.
    }
}
