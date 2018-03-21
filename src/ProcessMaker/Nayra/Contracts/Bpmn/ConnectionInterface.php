<?php

namespace ProcessMaker\Nayra\Contracts\Bpmn;

use ProcessMaker\Nayra\Bpmn\State;

/**
 * Interface to connect States and Transitions nodes.
 *
 * @package ProcessMaker\Nayra\Contracts\Bpmn
 */
interface ConnectionInterface
{

    /**
     * Get the origin node (state or transition) of the connection.
     *
     * @return ConnectionNodeInterface Origin element
     */
    public function origin();

    /**
     * Get the target node (state or transition) of the connection.
     *
     * @return ConnectionNodeInterface Target element
     */
    public function target();

    /**
     * Get the origin node of the connection as a state.
     *
     * @return StateInterface Origin element
     */
    public function originState();

    /**
     * Get the target node of the connection as a state.
     *
     * @return StateInterface Target element
     */
    public function targetState();
}
