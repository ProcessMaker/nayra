<?php

namespace ProcessMaker\Nayra\Contracts\Bpmn;

use ProcessMaker\Nayra\Contracts\Engine\ExecutionInstanceInterface;
use ProcessMaker\Nayra\Contracts\EventBusInterface;
use ProcessMaker\Nayra\Contracts\RepositoryInterface;
use ProcessMaker\Nayra\Contracts\Repositories\StorageInterface;

/**
 * Process describes a business work using a sequence or flow of activities.
 *
 * @package ProcessMaker\Nayra\Contracts\Bpmn
 */
interface ProcessInterface extends CallableElementInterface
{

    /**
     * Type of element.
     */
    const TYPE = 'process';

    /**
     * Properties.
     */
    const BPMN_PROPERTY_IS_CLOSED = 'isClosed';
    const BPMN_PROPERTY_IS_EXECUTABLE = 'isExecutable';
    const BPMN_PROPERTY_PROCESS_TYPE = 'processType';
    const BPMN_PROPERTY_PARTICIPANT = 'participant';
    const BPMN_PROPERTY_LANE_SET = 'laneSet';

    /**
     * Events defined for Activity
     */
    const EVENT_PROCESS_INSTANCE_CREATED = 'ProcessInstanceCreated';
    const EVENT_PROCESS_INSTANCE_COMPLETED = 'ProcessInstanceCompleted';

    /**
     * Get Diagram of the process.
     *
     * @return DiagramInterface
     */
    public function getDiagram();

    /**
     * Get Diagram of the process.
     *
     * @param DiagramInterface $diagram
     *
     * @return DiagramInterface
     */
    public function setDiagram(DiagramInterface $diagram);
    
    /**
     * Get Activities of the process.
     *
     * @return ActivityCollectionInterface
     */
    public function getActivities();

    /**
     * Get Activities of the process.
     *
     * @param ActivityCollectionInterface $activities
     *
     * @return ActivityCollectionInterface
     */
    public function setActivities(ActivityCollectionInterface $activities);
    
    /**
     * Get Gateways of the process.
     *
     * @return GatewayCollectionInterface
     */
    public function getGateways();

    /**
     * Get Gateways of the process.
     *
     * @param GatewayCollectionInterface $gateways
     *
     * @return GatewayCollectionInterface
     */
    public function setGateways(GatewayCollectionInterface $gateways);
    
    /**
     * Get Events of the process.
     *
     * @return EventCollectionInterface
     */
    public function getEvents();

    /**
     * Get Events of the process.
     *
     * @param EventCollectionInterface $events
     *
     * @return EventCollectionInterface
     */
    public function setEvents(EventCollectionInterface $events);
    
    /**
     * Get Artifacts of the process.
     *
     * @return ArtifactCollectionInterface
     */
    public function getArtifacts();

    /**
     * Get Artifacts of the process.
     *
     * @param ArtifactCollectionInterface $artifacts
     *
     * @return ArtifactCollectionInterface
     */
    public function setArtifacts(ArtifactCollectionInterface $artifacts);
    
    /**
     * Get Flows of the process.
     *
     * @return FlowCollectionInterface
     */
    public function getFlows();

    /**
     * Get Flows of the process.
     *
     * @param FlowCollectionInterface $flows
     *
     * @return FlowCollectionInterface
     */
    public function setFlows(FlowCollectionInterface $flows);
    
    /**
     * Get data stores of the process.
     *
     * @return DataStoreCollectionInterface
     */
    public function getDataStores();

    /**
     * Get data stores of the process.
     *
     * @param DataStoreCollectionInterface $dataStore
     *
     * @return DataStoreCollectionInterface
     */
    public function setDataStores(DataStoreCollectionInterface $dataStore);

    /**
     * Get the transition rules of the process.
     *
     * @param FactoryInterface $factory
     *
     * @return TransitionInterface[]
     */
    public function getTransitions(RepositoryInterface $factory);

    /**
     * Get the dispatcher of the process.
     *
     * @return \ProcessMaker\Nayra\Contracts\EventBusInterface
     */
    public function getDispatcher();

    /**
     * Set the dispatcher of the process.
     *
     * @param EventBusInterface $dispatcher
     *
     * @return $this
     */
    public function setDispatcher(EventBusInterface $dispatcher);

    /**
     * Add an activity to the process.
     *
     * @param ActivityInterface $activity
     *
     * @return $this
     */
    public function addActivity(ActivityInterface $activity);

    /**
     * Add an event to the process.
     *
     * @param EventInterface $event
     *
     * @return $this
     */
    public function addEvent(EventInterface $event);

    /**
     * Add a gateway to the process.
     *
     * @param GatewayInterface $gateway
     *
     * @return $this
     */
    public function addGateway(GatewayInterface $gateway);

    /**
     * Get the loaded process instances.
     *
     * @return \ProcessMaker\Nayra\Contracts\Engine\ExecutionInstanceInterface[]
     */
    public function getInstances();

    /**
     * Add process instance reference.
     *
     * @param \ProcessMaker\Nayra\Contracts\Engine\ExecutionInstanceInterface $instance
     *
     * @return $this
     */
    public function addInstance(ExecutionInstanceInterface $instance);

    /**
     * Get lane sets of the process.
     *
     * @return \ProcessMaker\Nayra\Contracts\Bpmn\LaneSetInterface[]
     */
    public function getLaneSets();

    /**
     * Set the lane sets of the process
     *
     * @param CollectionInterface $laneSets
     *
     * @return $this
     */
    public function setLaneSets(CollectionInterface $laneSets);

    /**
     * Notify an process instance event.
     *
     * @param string $eventName
     * @param \ProcessMaker\Nayra\Contracts\Engine\ExecutionInstanceInterface $instance
     * @param mixed $event
     *
     * @return $this
     */
    public function notifyInstanceEvent($eventName, ExecutionInstanceInterface $instance, $event = null);
}
