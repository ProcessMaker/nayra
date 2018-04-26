<?php

namespace ProcessMaker\Nayra\Contracts\Bpmn;

use ProcessMaker\Nayra\Contracts\Bpmn\CollectionInterface;
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
     * @param \ProcessMaker\Nayra\Contracts\Engine\ExecutionInstanceInterface $instance
     *
     * @return bool
     */
    public function consumeToken(TokenInterface $token/*, ExecutionInstanceInterface $instance*/);

    /**
     * Add a new token to the current state.
     *
     * @param \ProcessMaker\Nayra\Contracts\Engine\ExecutionInstanceInterface $instance
     *
     * @return bool
     */
    public function addNewToken(ExecutionInstanceInterface $instance);

    /**
     * Get the collection of tokens.
     *
     * @return CollectionInterface
     */
    public function getTokens();

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
