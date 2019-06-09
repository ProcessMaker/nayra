<?php

namespace ProcessMaker\Nayra\Bpmn;

use ProcessMaker\Nayra\Contracts\Bpmn\CollectionInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\FlowNodeInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\StateInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\TokenInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\TransitionInterface;
use ProcessMaker\Nayra\Contracts\Engine\ExecutionInstanceInterface;

/**
 * Trait to implement state of a node in which tokens can be received.
 *
 * @package ProcessMaker\Nayra\Bpmn
 */
trait StateTrait
{
    use BaseTrait,
        TraversableTrait,
        ObservableTrait;
    /**
     * Collection of tokens.
     *
     * @var Collection
     */
    private $tokens;

    /**
     * State name.
     *
     * @var string
     */
    private $name;

    /**
     * @var FlowNodeInterface $owner
     */
    private $owner;

    /**
     * Initialize the state object.
     *
     * @param FlowNodeInterface $owner
     * @param string $name
     */
    protected function initState(FlowNodeInterface $owner, $name = '')
    {
        $this->tokens = new Collection();
        $this->setRepository($owner->getRepository());
        $this->setName($name);
        $owner->addState($this);
        $this->setOwner($owner);
    }

    /**
     * Consume a token in the current state.
     *
     * @param TokenInterface $token
     *
     * @return bool
     */
    public function consumeToken(TokenInterface $token)
    {
        $tokenIndex = $this->tokens->indexOf($token);
        $valid = $tokenIndex !== false;
        if ($valid) {
            $this->tokens->splice($tokenIndex, 1);
            $this->notifyEvent(StateInterface::EVENT_TOKEN_CONSUMED, $token);
            $token->getInstance()->removeToken($token);
        }
        return $valid;
    }

    /**
     * Add a new token instance to the state.
     *
     * @param \ProcessMaker\Nayra\Contracts\Engine\ExecutionInstanceInterface|null $instance
     * @param array $properties
     * @param \ProcessMaker\Nayra\Contracts\Bpmn\TransitionInterface|null $source
     *
     * @return TokenInterface
     */
    public function addNewToken(ExecutionInstanceInterface $instance = null, array $properties = [], TransitionInterface $source = null)
    {
        $token = $this->getRepository()->getTokenRepository()->createTokenInstance();
        $token->setOwner($this);
        $token->setProperties($properties);
        $token->setOwner($this);
        $token->setInstance($instance);
        $this->getName() ? $token->setStatus($this->getName()) : '';
        !$instance ?: $instance->addToken($token);
        $this->tokens->push($token);
        $this->notifyEvent(StateInterface::EVENT_TOKEN_ARRIVED, $token, $source);
        return $token;
    }

    /**
     * Add a new token instance to the state.
     *
     * @param \ProcessMaker\Nayra\Contracts\Engine\ExecutionInstanceInterface $instance
     * @param \ProcessMaker\Nayra\Contracts\Bpmn\TokenInterface $token
     * @param boolean $skipEvents
     * @param \ProcessMaker\Nayra\Contracts\Bpmn\TransitionInterface|null $source
     *
     * @return TokenInterface
     */
    public function addToken(ExecutionInstanceInterface $instance, TokenInterface $token, $skipEvents = false, TransitionInterface $source = null)
    {
        $token->setOwner($this);
        $token->setInstance($instance);
        $this->getName() ? $token->setStatus($this->getName()) : '';
        $instance->addToken($token);
        $this->tokens->push($token);
        $skipEvents ?: $this->notifyEvent(StateInterface::EVENT_TOKEN_ARRIVED, $token, $source);
        return $token;
    }

    /**
     * Collection of tokens.
     *
     * @param ExecutionInstanceInterface|null $instance
     *
     * @return CollectionInterface
     */
    public function getTokens(ExecutionInstanceInterface $instance = null)
    {
        return $this->tokens->find(function (TokenInterface $token) use ($instance) {
            return $token->getInstance() === $instance;
        });
    }

    /**
     * Get state name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set state name.
     *
     * @param string $name
     *
     * @return $this
     */
    public function setName($name)
    {
        $this->name = $name;
        return $this;
    }

    /**
     * Set the owner node.
     *
     * @param \ProcessMaker\Nayra\Contracts\Bpmn\FlowNodeInterface $owner
     *
     * @return $this
     */
    public function setOwner(FlowNodeInterface $owner)
    {
        $this->owner = $owner;
        return $this;
    }

    /**
     * Get the owner node.
     *
     * @return \ProcessMaker\Nayra\Contracts\Bpmn\FlowNodeInterface
     */
    public function getOwner()
    {
        return $this->owner;
    }
}
