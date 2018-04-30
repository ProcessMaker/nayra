<?php

namespace ProcessMaker\Nayra\Bpmn;

use Illuminate\Contracts\Events\Dispatcher;
use ProcessMaker\Nayra\Bpmn\Collection;
use ProcessMaker\Nayra\Contracts\Bpmn\ActivityCollectionInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\ActivityInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\ArtifactCollectionInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\CollectionInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\DataStoreCollectionInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\DiagramInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\EndEventInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\EventCollectionInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\EventInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\FlowCollectionInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\FlowElementInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\FlowNodeInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\GatewayCollectionInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\GatewayInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\ProcessInterface;
use ProcessMaker\Nayra\Contracts\Engine\EngineInterface;
use ProcessMaker\Nayra\Contracts\Engine\ExecutionInstanceInterface;
use ProcessMaker\Nayra\Contracts\Repositories\RepositoryFactoryInterface;

trait ProcessTrait
{
    use BaseTrait, ObservableTrait {
        addProperty as baseAddProperty;
        notifyEvent as public;
    }

    /**
     * @var \Illuminate\Contracts\Events\Dispatcher
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

    protected function initProcessTrait()
    {
        $this->instances = new Collection;
    }

    /**
     *
     * @return \ProcessMaker\Nayra\Contracts\Bpmn\ActivityCollectionInterface
     */
    public function getActivities()
    {
        return $this->getProperty('activities');
    }

    public function getDataStores()
    {
        return $this->getProperty('dataStores');
    }

    public function getArtifacts()
    {
        return $this->getProperty('artifacts');
    }

    public function getDiagram()
    {
        return $this->getProperty('diagram');
    }

    public function getEvents()
    {
        return $this->getProperty('events');
    }

    public function getFlows()
    {
        return $this->getProperty('flows');
    }

    public function getGateways()
    {
        return $this->getProperty('gateways');
    }

    public function setActivities(ActivityCollectionInterface $activities)
    {
        $this->setProperty('activities', $activities);
        return $this;
    }

    public function setDataStores(DataStoreCollectionInterface $dataStores)
    {
        $this->setProperty('dataStores', $dataStores);
        return $this;
    }

    public function setArtifacts(ArtifactCollectionInterface $artifacts)
    {
        $this->setProperty('artifacts', $artifacts);
        return $this;
    }

    public function setDiagram(DiagramInterface $diagram)
    {
        $this->setProperty('diagram', $diagram);
        return $this;
    }

    public function setEvents(EventCollectionInterface $events)
    {
        $this->setProperty('events', $events);
        return $this;
    }

    public function setFlows(FlowCollectionInterface $flows)
    {
        $this->setProperty('flows', $flows);
        return $this;
    }

    public function setGateways(GatewayCollectionInterface $gateways)
    {
        $this->setProperty('gateways', $gateways);
        return $this;
    }

    /**
     * Add value to collection property and set the process as owner.
     *
     * @param $name
     * @param $value
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
     * @return CollectionInterface
     */
    public function getTransitions(RepositoryFactoryInterface $factory)
    {
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
        //Prepare the base events
        $this->attachEvent(EventInterface::EVENT_EVENT_TRIGGERED, function (EventInterface $event) {
            if (!($event instanceof EndEventInterface)) return;
            foreach ($this->getInstances() as $instance) {
                if ($instance->getTokens()->count() !== 0) continue;
                $this->notifyEvent(ProcessInterface::EVENT_PROCESS_COMPLETED, $this, $instance, $event);
                $arguments = [$this, $instance, $event];
                $bpmnEvents = $this->getBpmnEventClasses();
                if (isset($bpmnEvents[ProcessInterface::EVENT_PROCESS_COMPLETED])) {
                    $payload = new $bpmnEvents[ProcessInterface::EVENT_PROCESS_COMPLETED]($this, $arguments);
                } else {
                    $payload = ["object" => $this, "arguments" => $arguments];
                }
                $this->getDispatcher()->dispatch(ProcessInterface::EVENT_PROCESS_COMPLETED, $payload);
            }
        });
        return new Collection($transitions);
    }

    /**
     * @return $this
     */
    public function addActivity(ActivityInterface $activity)
    {
        $activity->setOwnerProcess($this);
        $this->getProperty('activities')->push($activity);
        return $this;
    }

    /**
     * @return $this
     */
    public function addEvent(EventInterface $event)
    {
        $event->setOwnerProcess($this);
        $this->getProperty('events')->push($event);
        return $this;
    }

    /**
     * @return $this
     */
    public function addGateway(GatewayInterface $gateway)
    {
        $gateway->setOwnerProcess($this);
        $this->getProperty('gateways')->push($gateway);
        return $this;
    }

    /**
     * @return \Illuminate\Contracts\Events\Dispatcher
     */
    public function getDispatcher()
    {
        return $this->dispatcher;
    }

    /**
     * @param Dispatcher $dispatcher
     *
     * @return $this
     */
    public function setDispatcher(Dispatcher $dispatcher)
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
     */
    public function call()
    {
        $dataStore = $this->getFactory()->getDataStoreRepository()->createDataStoreInstance();
        $instance = $this->getEngine()->createExecutionInstance($this, $dataStore);
        $this->getEvents()->find(function(FlowNodeInterface $event){
            if ($event->getIncomingFlows()->count()===0) {
                $event->start();
            }
        });
        return $instance;
    }
}