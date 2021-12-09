<?php

namespace ProcessMaker\Nayra\Bpmn;

use ProcessMaker\Nayra\Contracts\Bpmn\ConnectionInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\ConnectionNodeInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\StateInterface;

/**
 * Class that connect States and Transitions.
 *
 * @package ProcessMaker\Nayra\Bpmn
 */
class Connection implements ConnectionInterface
{
    /**
     * Origin node of the connection.
     *
     * @var \ProcessMaker\Nayra\Contracts\Bpmn\ConnectionNodeInterface
     */
    protected $origin;

    /**
     * Target node of the connection.
     *
     * @var \ProcessMaker\Nayra\Contracts\Bpmn\ConnectionNodeInterface
     */
    protected $target;

    /**
     * Connection constructor.
     *
     * @param ConnectionNodeInterface $origin
     * @param ConnectionNodeInterface $target
     */
    public function __construct(ConnectionNodeInterface $origin, ConnectionNodeInterface $target)
    {
        $this->origin = $origin;
        $this->target = $target;
    }

    /**
     * Get the origin node (state or transition) of the connection.
     *
     * @return ConnectionNodeInterface Origin element
     */
    public function origin()
    {
        return $this->origin;
    }

    /**
     * Get the target node (state or transition) of the connection.
     *
     * @return ConnectionNodeInterface Target element
     */
    public function target()
    {
        return $this->target;
    }

    /**
     * Get the origin node of the connection as a state.
     *
     * @return StateInterface Origin element
     */
    public function originState()
    {
        return $this->origin;
    }

    /**
     * Get the target node of the connection as a state.
     *
     * @return StateInterface Target element
     */
    public function targetState()
    {
        return $this->target;
    }
}
