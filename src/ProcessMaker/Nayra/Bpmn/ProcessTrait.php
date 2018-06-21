<?php

namespace ProcessMaker\Nayra\Bpmn;

use ProcessMaker\Nayra\Contracts\Bpmn\ActivityCollectionInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\ActivityInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\ArtifactCollectionInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\CollectionInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\DataStoreCollectionInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\DataStoreInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\DiagramInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\EndEventInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\EventCollectionInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\EventInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\FlowCollectionInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\FlowElementInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\GatewayCollectionInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\GatewayInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\ProcessInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\StartEventInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\TransitionInterface;
use ProcessMaker\Nayra\Contracts\Engine\EngineInterface;
use ProcessMaker\Nayra\Contracts\Engine\ExecutionInstanceInterface;
use ProcessMaker\Nayra\Contracts\EventBusInterface;
use ProcessMaker\Nayra\Contracts\RepositoryInterface;

/**
 * Process base implementation.
 *
 */
trait ProcessTrait
{
    use BaseTrait, ObservableTrait {
        addProperty as baseAddProperty;
        notifyEvent as public;
    }

    /**
     * @var \ProcessMaker\Nayra\Contracts\EventBusInterface
     */
    private $dispatcher;

    /**
     * @var ExecutionInstanceInterface[]
     */
    private $instances;

    /**
     * @var \ProcessMaker\Nayra\Contracts\Engine\EngineInterface
     */
    private $engine;

    /**
     * @var \ProcessMaker\Nayra\Contracts\Engine\TransitionInterface[] $transitions
     */
    private $transitions = null;

    /**
     * Initialize the process element.
     *
     */
    protected function initProcessTrait()
    {
        $this->instances = new Collection;
        $this->setLaneSets(new Collection);
    }

    /**
     *
     * @return \ProcessMaker\Nayra\Contracts\Bpmn\ActivityCollectionInterface
     */
    public function getActivities()
    {
        return $this->getProperty('activities');
    }

    /**
     * Get data stores.
     *
     * @return \ProcessMaker\Nayra\Contracts\Bpmn\DataStoreCollectionInterface
     */
    public function getDataStores()
    {
        return $this->getProperty('dataStores');
    }

    /**
     * Get artifacts.
     *
     * @return \ProcessMaker\Nayra\Contracts\Bpmn\ArtifactCollectionInterface
     */
    public function getArtifacts()
    {
        return $this->getProperty('artifacts');
    }

    /**
     * Get diagram
     *
     * @return \ProcessMaker\Nayra\Contracts\Bpmn\DiagramInterface
     */
    public function getDiagram()
    {
        return $this->getProperty('diagram');
    }

    /**
     * Get events.
     *
     * @return \ProcessMaker\Nayra\Contracts\Bpmn\EventCollectionInterface
     */
    public function getEvents()
    {
        return $this->getProperty('events');
    }

    /**
     * Get flows.
     *
     * @return \ProcessMaker\Nayra\Contracts\Bpmn\FlowCollectionInterface
     */
    public function getFlows()
    {
        return $this->getProperty('flows');
    }

    /**
     * Get gateways.
     *
     * @return \ProcessMaker\Nayra\Contracts\Bpmn\GatewayCollectionInterface
     */
    public function getGateways()
    {
        return $this->getProperty('gateways');
    }

    /**
     * Set activities collection.
     *
     * @param ActivityCollectionInterface $activities
     *
     * @return $this
     */
    public function setActivities(ActivityCollectionInterface $activities)
    {
        $this->setProperty('activities', $activities);
        return $this;
    }

    /**
     * Set data stores collection.
     *
     * @param DataStoreCollectionInterface $dataStores
     *
     * @return $this
     */
    public function setDataStores(DataStoreCollectionInterface $dataStores)
    {
        $this->setProperty('dataStores', $dataStores);
        return $this;
    }

    /**
     * Set artifacts collection.
     *
     * @param ArtifactCollectionInterface $artifacts
     *
     * @return $this
     */
    public function setArtifacts(ArtifactCollectionInterface $artifacts)
    {
        $this->setProperty('artifacts', $artifacts);
        return $this;
    }

    /**
     * Set diagram.
     *
     * @param DiagramInterface $diagram
     *
     * @return $this
     */
    public function setDiagram(DiagramInterface $diagram)
    {
        $this->setProperty('diagram', $diagram);
        return $this;
    }

    /**
     * Set events collection.
     *
     * @param EventCollectionInterface $events
     *
     * @return $this
     */
    public function setEvents(EventCollectionInterface $events)
    {
        $this->setProperty('events', $events);
        return $this;
    }

    /**
     * Set flows collection.
     *
     * @param FlowCollectionInterface $flows
     *
     * @return $this
     */
    public function setFlows(FlowCollectionInterface $flows)
    {
        $this->setProperty('flows', $flows);
        return $this;
    }

    /**
     * Get gateways collection.
     *
     * @param GatewayCollectionInterface $gateways
     *
     * @return $this
     */
    public function setGateways(GatewayCollectionInterface $gateways)
    {
        $this->setProperty('gateways', $gateways);
        return $this;
    }

    /**
     * Add value to collection property and set the process as owner.
     *
     * @param string $name
     * @param mixed $value
     *
     * @return $this
     */
    public function addProperty($name, $value)
    {
        $this->baseAddProperty($name, $value);
        if($value instanceof FlowElementInterface) {
            $value->setOwnerProcess($this);
        }
        return $this;
    }

    /**
     * Get transitions of the process.
     *
     * @param RepositoryInterface $factory
     *
     * @return CollectionInterface
     */
    public function getTransitions(RepositoryInterface $factory)
    {
        if ($this->transitions) {
            return $this->transitions;
        }
        //Build the runtime elements
        foreach($this->getProperty('events') as $event) {
            $event->buildTransitions($factory);
        }
        foreach($this->getProperty('activities') as $activity) {
            $activity->buildTransitions($factory);
        }
        foreach($this->getProperty('gateways') as $gateway) {
            $gateway->buildTransitions($factory);
        }
        //Build the runtime flows
        foreach($this->getProperty('events') as $event) {
            $event->buildFlowTransitions($factory);
        }
        foreach($this->getProperty('activities') as $activity) {
            $activity->buildFlowTransitions($factory);
        }
        foreach($this->getProperty('gateways') as $gateway) {
            $gateway->buildFlowTransitions($factory);
        }
        //Get the transitions
        $transitions = [];
        foreach($this->getProperty('events') as $event) {
            $transitions = array_merge($transitions, $event->getTransitions());
        }
        foreach($this->getProperty('activities') as $activity) {
            $transitions = array_merge($transitions, $activity->getTransitions());
        }
        foreach($this->getProperty('gateways') as $gateway) {
            $transitions = array_merge($transitions, $gateway->getTransitions());
        }
        //Catch the conclusion of a process
        $this->attachEvent(EventInterface::EVENT_EVENT_TRIGGERED, function (EventInterface $event, TransitionInterface $transition, CollectionInterface $tokens) {
            if ($tokens->count() === 0 || !$event instanceof EndEventInterface) {
                return;
            }
            $instance = $tokens->item(0)->getInstance();
            if ($instance->getTokens()->count() !== 0) {
                return;
            }
            $this->notifyInstanceEvent(ProcessInterface::EVENT_PROCESS_INSTANCE_COMPLETED, $instance, $event);
        });
        $this->transitions = new Collection($transitions);
        return $this->transitions;
    }

    /**
     * Notify an process instance event.
     *
     * @param string $eventName
     * @param \ProcessMaker\Nayra\Contracts\Engine\ExecutionInstanceInterface $instance
     * @param mixed $event
     *
     * @return $this
     */
    public function notifyInstanceEvent($eventName, ExecutionInstanceInterface $instance, $event = null)
    {
        $this->notifyEvent($eventName, $this, $instance, $event);
        $arguments = [$this, $instance, $event];
        $bpmnEvents = $this->getBpmnEventClasses();
        $payload = new $bpmnEvents[$eventName](...$arguments);
        $this->getDispatcher()->dispatch($eventName, $payload);
        return $this;
    }

    /**
     * Add an activity.
     *
     * @param \ProcessMaker\Nayra\Contracts\Bpmn\ActivityInterface $activity
     * @return $this
     */
    public function addActivity(ActivityInterface $activity)
    {
        $activity->setOwnerProcess($this);
        $this->getProperty('activities')->push($activity);
        return $this;
    }

    /**
     * Add an event
     *
     * @param \ProcessMaker\Nayra\Contracts\Bpmn\EventInterface $event
     *
     * @return $this
     */
    public function addEvent(EventInterface $event)
    {
        $event->setOwnerProcess($this);
        $this->getProperty('events')->push($event);
        return $this;
    }

    /**
     * Add a gateway
     *
     * @param \ProcessMaker\Nayra\Contracts\Bpmn\GatewayInterface $gateway
     *
     * @return $this
     */
    public function addGateway(GatewayInterface $gateway)
    {
        $gateway->setOwnerProcess($this);
        $this->getProperty('gateways')->push($gateway);
        return $this;
    }

    /**
     * @param \ProcessMaker\Nayra\Contracts\EventBusInterface $dispatcher
     *
     * @return \ProcessMaker\Nayra\Contracts\EventBusInterface
     */
    public function getDispatcher()
    {
        return $this->dispatcher;
    }

    /**
     * @param \ProcessMaker\Nayra\Contracts\EventBusInterface $dispatcher
     *
     * @return $this
     */
    public function setDispatcher(EventBusInterface $dispatcher)
    {
        $this->dispatcher = $dispatcher;
        return $this;
    }

    /**
     * Get the loaded process instances.
     *
     * @return \ProcessMaker\Nayra\Contracts\Engine\ExecutionInstanceInterface[]
     */
    public function getInstances()
    {
        return $this->instances;
    }

    /**
     * Add process instance reference.
     *
     * @param ExecutionInstanceInterface $instance
     *
     * @return $this
     */
    public function addInstance(ExecutionInstanceInterface $instance)
    {
        $this->instances->push($instance);
        return $this;
    }

    /**
     * Set the engine that controls the elements.
     *
     * @param EngineInterface $engine
     *
     * @return EngineInterface
     */
    public function setEngine(EngineInterface $engine)
    {
        $this->engine = $engine;
        return $this;
    }

    /**
     * Get the engine that controls the elements.
     *
     * @return EngineInterface
     */
    public function getEngine()
    {
        return $this->engine;
    }

    /**
     * Create an instance of the callable element and start it.
     *
     * @param DataStoreInterface|null $dataStore
     *
     * @return ExecutionInstanceInterface
     */
    public function call(DataStoreInterface $dataStore = null)
    {
        if (empty($dataStore)) {
            $dataStore = $this->getRepository()->createDataStore();
        }
        $instance = $this->getEngine()->createExecutionInstance($this, $dataStore);
        $this->getEvents()->find(function(EventInterface $event){
            if ($event instanceof StartEventInterface && $event->getEventDefinitions()->count() === 0) {
                $event->start();
            }
        });
        return $instance;
    }

    /**
     * Get the lane sets of the process.
     *
     * @return \ProcessMaker\Nayra\Contracts\Bpmn\LaneSetInterface[]
     */
    public function getLaneSets()
    {
        return $this->getProperty(ProcessInterface::BPMN_PROPERTY_LANE_SET);
    }

    /**
     * Set the lane sets of the process
     *
     * @param CollectionInterface $laneSets
     *
     * @return $this
     */
    public function setLaneSets(CollectionInterface $laneSets)
    {
        $this->setProperty(ProcessInterface::BPMN_PROPERTY_LANE_SET, $laneSets);
        return $this;
    }
}
