<?php

namespace ProcessMaker\Models;

use ProcessMaker\Nayra\Bpmn\ProcessTrait;
use ProcessMaker\Nayra\Contracts\Bpmn\ProcessInterface;

/**
 * Process implementation
 *
 * @package ProcessMaker\Models
 */
class Process implements ProcessInterface
{

    use ProcessTrait,
        LocalPropertiesTrait;

    /**
     * Process constructor.
     */
    public function __construct()
    {
        $this->setActivities(new ActivityCollection);
        $this->setGateways(new GatewayCollection);
        $this->setEvents(new EventCollection);
        $this->setFlows(new FlowCollection);
        $this->setArtifacts(new ArtifactCollection);
        $this->setDataStores(new DataStoreCollection);
    }

    /**
     * Get custom property map.
     *
     * @return array
     */
    protected function customProperties()
    {
        return [
            ProcessInterface::BPMN_PROPERTY_ID        => 'id',
            ProcessInterface::BPMN_PROPERTY_IS_CLOSED => 'isClosed',
        ];
    }
}
