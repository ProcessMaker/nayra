<?php

namespace ProcessMaker\Nayra\Contracts\Repositories;

use ProcessMaker\Nayra\Contracts\Bpmn\EventInterface;

/**
 * Repository for EventInterface
 *
 * @package ProcessMaker\Nayra\Contracts\Repositories
 */
interface EventRepositoryInterface extends RepositoryInterface
{

    /**
     * Create an event instance.
     *
     * @return \ProcessMaker\Nayra\Contracts\Bpmn\EventInterface
     */
    public function createEventInstance();

    /**
     * Load a event from a persistent storage.
     *
     * @param string $uid
     *
     * @return \ProcessMaker\Nayra\Contracts\Bpmn\EventInterface
     */
    public function loadEventByUid($uid);

    /**
     * Create or update an event to a persistent storage.
     *
     * @param \ProcessMaker\Nayra\Contracts\Bpmn\EventInterface $event
     * @param $saveChildElements
     *
     * @return $this
     */
    public function store(EventInterface $event, $saveChildElements=false);
}
