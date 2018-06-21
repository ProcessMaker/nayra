<?php

namespace ProcessMaker\Test\Models;

use ProcessMaker\Models\ActivityCollection;
use ProcessMaker\Models\ArtifactCollection;
use ProcessMaker\Models\DataStoreCollection;
use ProcessMaker\Models\EventCollection;
use ProcessMaker\Models\FlowCollection;
use ProcessMaker\Models\GatewayCollection;
use ProcessMaker\Models\ProcessCompletedEvent;
use ProcessMaker\Nayra\Bpmn\ProcessTrait;
use ProcessMaker\Nayra\Contracts\Bpmn\ProcessInterface;

/**
 * Process implementation
 *
 * @package ProcessMaker\Models
 */
class CustomProcess implements ProcessInterface
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
     * Defined BPMN event classes.
     *
     * @return array
     */
    protected function getBpmnEventClasses()
    {
        return [
            ProcessInterface::EVENT_PROCESS_INSTANCE_COMPLETED => ProcessCompletedEvent::class
        ];
    }
}
