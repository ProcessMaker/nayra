<?php

namespace ProcessMaker\Nayra\Contracts\Repositories;

use ProcessMaker\Nayra\Contracts\Bpmn\EndEventInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\ErrorInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\ExclusiveGatewayInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\IntermediateCatchEventInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\MessageInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\ScriptTaskInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\SignalInterface;
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
     * @param EndEventInterface $event
     * @param TokenInterface $token
     *
     * @return mixed
     */
    public function persistThrowEventTokenArrives(EndEventInterface $event, TokenInterface $token);

    /**
     * @param EndEventInterface $endEvent
     * @param TokenInterface $token
     *
     * @return mixed
     */
    public function persistThrowEventTokenConsumed(EndEventInterface $endEvent, TokenInterface $token);

    /**
     * @param ScriptTaskInterface $scriptTask
     * @param TokenInterface $token
     *
     * @return mixed
     */
    public function persistScriptTaskActivated(ScriptTaskInterface $scriptTask, TokenInterface $token);

    /**
     * @param EndEventInterface $endEvent
     * @param TokenInterface $token
     * @param ErrorInterface $error
     *
 *
     * @return mixed
     */
    public function persistThrowErrorEvent(EndEventInterface $endEvent, TokenInterface $token, ErrorInterface $error);

    /**
     * @param ExclusiveGatewayInterface $exclusiveGateway
     * @param TokenInterface $token
     *
     * @return mixed
     */
    public function persistGatewayTokenArrives(ExclusiveGatewayInterface $exclusiveGateway, TokenInterface $token);

    /**
     * @param ExclusiveGatewayInterface $exclusiveGateway
     *
     * @return mixed
     */
    public function persistGatewayActivated(ExclusiveGatewayInterface $exclusiveGateway);

    /**
     * @param ExclusiveGatewayInterface $exclusiveGateway
     * @param TokenInterface $token
     *
     * @return mixed
     */
    public function persistGatewayTokenConsumed(ExclusiveGatewayInterface $exclusiveGateway, TokenInterface $token);

    /**
     * @param ExclusiveGatewayInterface $exclusiveGateway
     *
     * @return mixed
     */
    public function persistGatewayTokenPassed(ExclusiveGatewayInterface $exclusiveGateway);

    /**
     * @param IntermediateCatchEventInterface $intermediateCatchEvent
     * @param TokenInterface $token
     *
     * @return mixed
     */
    public function persistCatchEventTokenArrives(IntermediateCatchEventInterface $intermediateCatchEvent, TokenInterface $token);

    /**
     * @param IntermediateCatchEventInterface $intermediateCatchEvent
     * @param TokenInterface $token
     *
     * @return mixed
     */
    public function persistCatchEventTokenCatch(IntermediateCatchEventInterface $intermediateCatchEvent, TokenInterface $token);

    /**
     * @param IntermediateCatchEventInterface $intermediateCatchEvent
     * @param TokenInterface $token
     *
     * @return mixed
     */
    public function persistCatchEventTokenConsumed(IntermediateCatchEventInterface $intermediateCatchEvent, TokenInterface $token);

    /**
     * @param IntermediateCatchEventInterface $intermediateCatchEvent
     *
     * @return mixed
     */
    public function persistCatchEventTokenPassed(IntermediateCatchEventInterface $intermediateCatchEvent);

    /**
     * @param IntermediateCatchEventInterface $intermediateThrowEvent
     *
     * @return mixed
     */
    public function persistThrowEventTokenPassed(IntermediateCatchEventInterface $intermediateThrowEvent);

    /**
     * @param EndEventInterface $endEvent
     * @param TokenInterface $token
     * @param MessageInterface $message
     *
     * @return mixed
     */
    public function persistThrowMessageEvent(EndEventInterface $endEvent, TokenInterface $token, MessageInterface $message);

    /**
     * @param EndEventInterface $endEvent
     * @param TokenInterface $token
     * @param SignalInterface $message
     *
     * @return mixed
     */
    public function persistThrowSignalEvent(EndEventInterface $endEvent, TokenInterface $token, SignalInterface $message);

    /**
     * @param EndEventInterface $endEvent
     * @param TokenInterface $token
     * @param $param
     *
     * @return mixed
     */
    public function persistThrowTerminateEvent(EndEventInterface $endEvent, TokenInterface $token, $param);
}
