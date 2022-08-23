<?php

namespace ProcessMaker\Nayra\Contracts\Bpmn;

use ProcessMaker\Nayra\Bpmn\Collection;

/**
 * Connection node (States and transitions) that define the behavior of
 * a bpmn flow node.
 */
interface ConnectionNodeInterface extends EntityInterface
{
    /**
     * @return Collection Outgoing flows.
     */
    public function outgoing();

    /**
     * @return Collection Incoming flows.
     */
    public function incoming();

    /**
     * Add an outgoing flow.
     *
     * @param \ProcessMaker\Nayra\Contracts\Bpmn\ConnectionNodeInterface $target
     */
    public function connectTo(self $target);
}
