<?php

namespace ProcessMaker\Nayra\Contracts\Repositories;

use ProcessMaker\Nayra\Contracts\Bpmn\FlowInterface;

/**
 * Repository for FlowInterface
 *
 * @package ProcessMaker\Nayra\Contracts\Repositories
 */
interface FlowRepositoryInterface extends RepositoryInterface
{

    /**
     * Create a flow instance.
     *
     * @return \ProcessMaker\Nayra\Contracts\Bpmn\FlowInterface
     */
    public function createFlowInstance();

    /**
     * Load a flow from a persistent storage.
     *
     * @param string $uid
     *
     * @return \ProcessMaker\Nayra\Contracts\Bpmn\FlowInterface
     */
    public function loadFlowByUid($uid);

    /**
     * Create or update a flow to a persistent storage.
     *
     * @param \ProcessMaker\Nayra\Contracts\Bpmn\FlowInterface $flow
     * @param $saveChildElements
     *
     * @return $this
     */
    public function store(FlowInterface $flow, $saveChildElements=false);
}
