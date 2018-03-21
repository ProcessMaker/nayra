<?php

namespace ProcessMaker\Nayra\Bpmn;

use ProcessMaker\Nayra\Contracts\Bpmn\EntityInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\TokenInterface;
use ProcessMaker\Nayra\Bpmn\EntityTrait;
use ProcessMaker\Nayra\Bpmn\Collection;
use ProcessMaker\Nayra\Bpmn\ObservableTrait;
use ProcessMaker\Nayra\Bpmn\FlowElementTrait;
use ProcessMaker\Nayra\Bpmn\TokenTrait;
use ProcessMaker\Nayra\Contracts\Bpmn\CollectionInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\StateInterface;
use ProcessMaker\Nayra\Bpmn\TraversableTrait;

/**
 * Trait to implement state of a node in which tokens can be received.
 *
 * @package ProcessMaker\Nayra\Bpmn
 */
trait StateTrait
{

    use FlowElementTrait,
        TraversableTrait,
        ObservableTrait;
    /**
     * Collection of tokens.
     *
     * @var Collection
     */
    private $tokens;

    protected function initPlaceBehavior(EntityInterface $owner)
    {
        $this->tokens = new Collection();
        $this->setFactory($owner->getFactory());
    }

    /**
     *
     * @return bool
     */
    public function consumeToken(TokenInterface $token)
    {
        $tokenIndex = $this->tokens->indexOf($token);
        if ($tokenIndex !== false) {
            $this->tokens->splice($tokenIndex, 1);
            $this->notifyEvent(StateInterface::EVENT_TOKEN_CONSUMED, $token);
        }
        return $tokenIndex !== false;
    }

    /**
     *
     * @return bool
     */
    public function addNewToken()
    {
        $token = $this->getFactory()->getTokenRepository()->createTokenInstance($this);
        $token->setOwner($this);
        //new TokenTrait($this);
        $this->tokens->push($token);
        $this->notifyEvent(StateInterface::EVENT_TOKEN_ARRIVED, $token);
        return true;
    }

    /**
     * Collection of tokens.
     *
     * @return CollectionInterface
     */
    public function getTokens()
    {
        return $this->tokens;
    }
}
