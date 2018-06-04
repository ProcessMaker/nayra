<?php

namespace ProcessMaker\Nayra\Bpmn\Model;

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

    protected function getBpmnEventClasses()
    {
        return [];
    }
}