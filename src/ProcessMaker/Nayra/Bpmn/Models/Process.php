<?php

namespace ProcessMaker\Nayra\Bpmn\Models;

use ProcessMaker\Nayra\Bpmn\Events\ProcessInstanceCompletedEvent;
use ProcessMaker\Nayra\Bpmn\Events\ProcessInstanceCreatedEvent;
use ProcessMaker\Nayra\Bpmn\Models\ActivityCollection;
use ProcessMaker\Nayra\Bpmn\Models\ArtifactCollection;
use ProcessMaker\Nayra\Bpmn\Models\DataStoreCollection;
use ProcessMaker\Nayra\Bpmn\Models\EventCollection;
use ProcessMaker\Nayra\Bpmn\Models\FlowCollection;
use ProcessMaker\Nayra\Bpmn\Models\GatewayCollection;
use ProcessMaker\Nayra\Bpmn\ProcessTrait;
use ProcessMaker\Nayra\Contracts\Bpmn\ProcessInterface;

/**
 * Process implementation
 *
 * @package ProcessMaker\Models
 */
class Process implements ProcessInterface
{

    use ProcessTrait;

    /**
     * Process constructor.
     *
     * @param array ...$args
     */
    public function __construct(...$args)
    {
        $this->bootElement($args);
        $this->setActivities(new ActivityCollection);
        $this->setGateways(new GatewayCollection);
        $this->setEvents(new EventCollection);
        $this->setFlows(new FlowCollection);
        $this->setArtifacts(new ArtifactCollection);
        $this->setDataStores(new DataStoreCollection);
    }

    /**
     * Get BPMN event classes.
     *
     * @return array
     */
    protected function getBpmnEventClasses()
    {
        return [
            static::EVENT_PROCESS_INSTANCE_COMPLETED => ProcessInstanceCompletedEvent::class,
            static::EVENT_PROCESS_INSTANCE_CREATED   => ProcessInstanceCreatedEvent::class,
        ];
    }
}
