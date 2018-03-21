<?php

namespace ProcessMaker\Nayra\Contracts\Bpmn;

use Illuminate\Contracts\Events\Dispatcher;
use ProcessMaker\Nayra\Contracts\Repositories\RepositoryFactoryInterface;

/**
 * Process describes a business work using a sequence or flow of activities.
 *
 * @package ProcessMaker\Nayra\Contracts\Bpmn
 */
interface ProcessInterface extends EntityInterface
{

    /**
     * Type of element.
     */
    const TYPE = 'bpmnProcess';


    /**
     * Properties.
     */
    const PROPERTIES = [
        'PRO_UID' => '',
        'PRO_ID' => NULL,
        'PRO_TITLE' => NULL,
        'PRO_DESCRIPTION' => NULL,
        'PRO_PARENT' => '0',
        'PRO_TIME' => '1',
        'PRO_TIMEUNIT' => 'DAYS',
        'PRO_STATUS' => 'ACTIVE',
        'PRO_TYPE_DAY' => '0',
        'PRO_TYPE' => 'NORMAL',
        'PRO_ASSIGNMENT' => 'FALSE',
        'PRO_SHOW_MAP' => '1',
        'PRO_SHOW_MESSAGE' => '1',
        'PRO_SUBPROCESS' => '0',
        'PRO_TRI_CREATE' => '',
        'PRO_TRI_OPEN' => '',
        'PRO_TRI_DELETED' => '',
        'PRO_TRI_CANCELED' => '',
        'PRO_TRI_PAUSED' => '',
        'PRO_TRI_REASSIGNED' => '',
        'PRO_TRI_UNPAUSED' => '',
        'PRO_TYPE_PROCESS' => 'PUBLIC',
        'PRO_SHOW_DELEGATE' => '1',
        'PRO_SHOW_DYNAFORM' => '0',
        'PRO_CATEGORY' => '',
        'PRO_SUB_CATEGORY' => '',
        'PRO_INDUSTRY' => '1',
        'PRO_UPDATE_DATE' => NULL,
        'PRO_CREATE_DATE' => NULL,
        'PRO_CREATE_USER' => '',
        'PRO_HEIGHT' => '5000',
        'PRO_WIDTH' => '10000',
        'PRO_TITLE_X' => '0',
        'PRO_TITLE_Y' => '6',
        'PRO_DEBUG' => '0',
        'PRO_DYNAFORMS' => NULL,
        'PRO_DERIVATION_SCREEN_TPL' => '',
        'PRO_COST' => '0.00',
        'PRO_UNIT_COST' => '',
        'PRO_ITEE' => '0',
        'PRO_ACTION_DONE' => NULL
    ];

    /**
     * Child elements.
     */
    const ELEMENTS = [
        'activities' => ActivityInterface::TYPE,
        'gateways' => GatewayInterface::TYPE,
        'events' => EventNodeInterface::TYPE,
        'artifacts' => ArtifactInterface::TYPE,
        'flows' => FlowInterface::TYPE,
        'dataStores' => DataStoreInterface::TYPE,
    ];

    
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
     * @param RepositoryFactoryInterface $factory
     *
     * @return TransitionInterface[]
     */
    public function getTransitions(RepositoryFactoryInterface $factory);

    /**
     * Get the dispatcher of the process.
     *
     * @return \Illuminate\Contracts\Events\Dispatcher
     */
    public function getDispatcher();

    /**
     * Set the dispatcher of the process.
     *
     * @param Dispatcher $dispatcher
     *
     * @return $this
     */
    public function setDispatcher(Dispatcher $dispatcher);

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
     * @param EventNodeInterface $event
     *
     * @return $this
     */
    public function addEvent(EventNodeInterface $event);

    /**
     * Add a gateway to the process.
     *
     * @param GatewayInterface $gateway
     *
     * @return $this
     */
    public function addGateway(GatewayInterface $gateway);
}
