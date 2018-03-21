<?php

namespace ProcessMaker\Models;

use ProcessMaker\Nayra\Contracts\Bpmn\EventNodeInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\ProcessInterface;
use ProcessMaker\Nayra\Bpmn\RepositoryTrait;
use ProcessMaker\Nayra\Contracts\Repositories\EventRepositoryInterface;

/**
 * EventRepository
 *
 * @package ProcessMaker\Models
 */
class EventRepository implements EventRepositoryInterface
{
    use RepositoryTrait;

    /**
     * Create a start event.
     *
     * @param array $properties
     * @return StartEvent
     */
    public function createStartEventInstance($properties = [])
    {
        $event = new StartEvent($properties);
        $event->setFactory($this->getFactory());
        return $event;
    }

    /**
     * Create an end event.
     *
     * @param array $properties
     * @return EndEvent
     */
    public function createEndEventInstance($properties = [])
    {
        $event = new EndEvent($properties);
        $event->setFactory($this->getFactory());
        return $event;
    }

    /**
     * Create a new event.
     *
     * @return \ProcessMaker\Nayra\Contracts\Bpmn\EventNodeInterface
     */
    public function createEventInstance()
    {

    }

    /**
     * Load a event from a persistent storage.
     *
     * @param string $uid
     *
     * @return \ProcessMaker\Nayra\Contracts\Bpmn\EventNodeInterface
     */
    public function loadEventByUid($uid)
    {

    }

    /**
     * Create or update an event to a persistent storage.
     *
     * @param \ProcessMaker\Nayra\Contracts\Bpmn\EventNodeInterface $event
     * @param $saveChildElements
     *
     * @return $this
     */
    public function store(EventNodeInterface $event, $saveChildElements = false)
    {

    }

    /**
     * Create an instance of the entity.
     *
     * @param string $uid
     *
     * @return \ProcessMaker\Nayra\Contracts\Bpmn\EntityInterface
     */
    public function create(ProcessInterface $process = null)
    {

    }
}
