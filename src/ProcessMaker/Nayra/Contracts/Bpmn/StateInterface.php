<?php

namespace ProcessMaker\Nayra\Contracts\Bpmn;

use ProcessMaker\Nayra\Contracts\Engine\ExecutionInstanceInterface;

/**
 * State of a node in which tokens can be received.
 *
 * @package ProcessMaker\Nayra\Contracts\Bpmn
 */
interface StateInterface extends TraversableInterface, ObservableInterface, ConnectionNodeInterface
{
    const EVENT_TOKEN_ARRIVED = 'TokenArrived';
    const EVENT_TOKEN_CONSUMED = 'TokenConsumed';

    /**
     * Consume a token from the current state.
     *
     * @param \ProcessMaker\Nayra\Contracts\Bpmn\TokenInterface $token
     *
     * @return bool
     */
    public function consumeToken(TokenInterface $token);

    /**
     * Add a new token to the current state.
     *
     * @param \ProcessMaker\Nayra\Contracts\Engine\ExecutionInstanceInterface|null $instance
     * @param array $properties
     * @param \ProcessMaker\Nayra\Contracts\Bpmn\TransitionInterface|null $source
     *
     * @return TokenInterface
     */
    public function addNewToken(ExecutionInstanceInterface $instance = null, array $properties = [], TransitionInterface $source = null);

    /**
     * Add a new token to the current state.
     *
     * @param \ProcessMaker\Nayra\Contracts\Engine\ExecutionInstanceInterface $instance
     * @param \ProcessMaker\Nayra\Contracts\Bpmn\TokenInterface $token
     * @param bool $skipEvents
     * @param \ProcessMaker\Nayra\Contracts\Bpmn\TransitionInterface|null $source
     *
     * @return bool
     */
    public function addToken(ExecutionInstanceInterface $instance, TokenInterface $token, $skipEvents = false, TransitionInterface $source = null);

    /**
     * Get the collection of tokens.
     *
     * @param ExecutionInstanceInterface|null $instance
     *
     * @return CollectionInterface
     */
    public function getTokens(ExecutionInstanceInterface $instance = null);

    /**
     * Get state name
     *
     * @return string
     */
    public function getName();

    /**
     * Set state name.
     *
     * @param string $name
     *
     * @return $this
     */
    public function setName($name);
}
