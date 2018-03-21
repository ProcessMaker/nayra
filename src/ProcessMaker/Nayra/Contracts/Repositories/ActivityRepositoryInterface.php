<?php

namespace ProcessMaker\Nayra\Contracts\Repositories;

use ProcessMaker\Nayra\Contracts\Bpmn\ActivityInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\ProcessInterface;

/**
 * Repository for ActivityInterface
 *
 * @package ProcessMaker\Nayra\Contracts\Repositories
 */
interface ActivityRepositoryInterface extends RepositoryInterface
{

    /**
     * Create an activity instance.
     *
     * @param string $uid
     *
     * @return \ProcessMaker\Nayra\Contracts\Bpmn\ActivityInterface
     */
    public function createActivityInstance(ProcessInterface $process=null);

    /**
     * Load a activity from a persistent storage.
     *
     * @param string $uid
     *
     * @return \ProcessMaker\Nayra\Contracts\Bpmn\ActivityInterface
     */
    public function loadActivityByUid($uid);

    /**
     * Create or update a activity to a persistent storage.
     *
     * @param \ProcessMaker\Nayra\Contracts\Bpmn\ActivityInterface $activity
     * @param $saveChildElements
     *
     * @return $this
     */
    public function store(ActivityInterface $activity, $saveChildElements=false);
}
