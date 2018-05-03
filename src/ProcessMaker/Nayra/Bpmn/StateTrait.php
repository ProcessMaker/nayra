<?php

namespace ProcessMaker\Nayra\Bpmn;

use ProcessMaker\Nayra\Bpmn\BaseTrait;
use ProcessMaker\Nayra\Bpmn\Collection;
use ProcessMaker\Nayra\Bpmn\ObservableTrait;
use ProcessMaker\Nayra\Bpmn\TraversableTrait;
use ProcessMaker\Nayra\Contracts\Bpmn\CollectionInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\FlowNodeInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\StateInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\TokenInterface;
use ProcessMaker\Nayra\Contracts\Engine\ExecutionInstanceInterface;
use ProcessMaker\Nayra\Exceptions\UnableConsumeTokenException;

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
     * Initialize the state object.
     *
     * @param EntityInterface $owner
     * @param string $name
     */
    protected function initState(FlowNodeInterface $owner, $name)
    {
        $this->tokens = new Collection();
        $this->setFactory($owner->getFactory());
        $this->setName($name);
        $owner->addState($this);
    }

    /**
     * Consume a token in the current state.
     *
     * @param TokenInterface $token
     * @return bool
     *
     * @throws UnableConsumeTokenException
     */
    public function consumeToken(TokenInterface $token)
    {
        $tokenIndex = $this->tokens->indexOf($token);
        $valid = $tokenIndex !== false;
        if (!$valid) {
            throw new UnableConsumeTokenException($token, $this);
        }
        $this->tokens->splice($tokenIndex, 1);
        $this->notifyEvent(StateInterface::EVENT_TOKEN_CONSUMED, $token);
        $token->getInstance()->removeToken($token);
        return $valid;
    }

    /**
     * Add a new token instance to the state.
     *
     * @param \ProcessMaker\Nayra\Contracts\Engine\ExecutionInstanceInterface $instance
     *
     * @return bool
     */
    public function addNewToken(ExecutionInstanceInterface $instance)
    {
        $token = $this->getFactory()->getTokenRepository()->createTokenInstance($this);
        $token->setOwner($this);
        $token->setInstance($instance);
        $token->setStatus($this->getName());
        $instance->addToken($token);
        $this->tokens->push($token);
        $this->notifyEvent(StateInterface::EVENT_TOKEN_ARRIVED, $token);
        return true;
    }

    /**
     * Collection of tokens.
     *
     * @return CollectionInterface
     */
    public function getTokens(ExecutionInstanceInterface $instance)
    {
        return $this->tokens->find(function(TokenInterface $token) use ($instance) {
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
}
