<?php

namespace ProcessMaker\Nayra\Contracts\Bpmn;

use ProcessMaker\Nayra\Contracts\Bpmn\CollectionInterface;

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
     * @return bool
     */
    public function consumeToken(TokenInterface $token);

    /**
     * Add a new token to the current state.
     *
     * @return bool
     */
    public function addNewToken();

    /**
     * Get the collection of tokens.
     *
     * @return CollectionInterface
     */
    public function getTokens();
}
