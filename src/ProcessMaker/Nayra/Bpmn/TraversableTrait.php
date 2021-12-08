<?php

namespace ProcessMaker\Nayra\Bpmn;

use ProcessMaker\Nayra\Contracts\Bpmn\ConnectionInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\ConnectionNodeInterface;

/**
 * Implements the search of paths through elements.
 *
 * @package ProcessMaker\Nayra\Bpmn
 */
trait TraversableTrait
{

    /**
     * Collection of incoming flows.
     *
     * @var Collection
     */
    private $incoming;
    private $outgoing;
    private $mainConnection;

    /**
     * Initialize incoming/outgoing flows
     *
     */
    protected function initFlowElementBehavior()
    {
        $this->outgoing = new Collection;
        $this->incoming = new Collection;
    }

    /**
     * This attribute identifies the outgoing Sequence Flow of the FlowNode.
     *
     * @return Collection
     */
    public function outgoing()
    {
        return $this->outgoing;
    }

    /**
     * This attribute identifies the incoming Sequence Flow of the FlowNode.
     *
     * @return Collection
     */
    public function incoming()
    {
        return $this->incoming;
    }

    /**
     * @param ConnectionNodeInterface $target
     *
     * @return ConnectionInterface
     */
    public function connectTo(ConnectionNodeInterface $target)
    {
        $flow = new Connection($this, $target);
        $this->outgoing()->push($flow);
        $target->incoming()->push($flow);
        return $flow;
    }

    /**
     * Find all the paths that complies with the $condition and $while.
     *
     * @param callable $condition
     * @param callable $while
     * @param array $path
     * @param array $passedthru
     * @param array $paths
     *
     * @return Collection
     */
    public function paths(callable $condition, callable $while, $path = [], &$passedthru = [], &$paths = [])
    {
        $this->incoming()->find(function ($flow) use ($condition, $while, $path, &$passedthru, &$paths) {
            if (array_search($flow, $passedthru, true) !== false) {
                return;
            }
            $passedthru[] = $flow;
            if ($condition($flow)) {
                $path[] = $flow;
                $paths[] = new Path($path);
            } elseif ($while($flow)) {
                $path[] = $flow;
                $flow->origin()->paths($condition, $while, $path, $passedthru, $paths);
            }
        });
        return new Collection($paths);
    }

    /**
     * Set main connection of the node.
     *
     * @param \ProcessMaker\Nayra\Contracts\Bpmn\ConnectionInterface $connection
     *
     * @return \ProcessMaker\Nayra\Contracts\Bpmn\ConnectionNodeInterface
     */
    public function setMainConnection(ConnectionInterface $connection)
    {
        $this->mainConnection = $connection;
    }

    /**
     * Get main connection of the node.
     *
     * @return \ProcessMaker\Nayra\Contracts\Bpmn\ConnectionInterface $connection
     */
    protected function getMainConnection()
    {
        return $this->mainConnection;
    }
}
