<?php

namespace ProcessMaker\Nayra\Bpmn;

use Illuminate\Contracts\Events\Dispatcher;
use ProcessMaker\Nayra\Contracts\Bpmn\ActivityCollectionInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\ActivityInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\ArtifactCollectionInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\CollectionInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\DataStoreCollectionInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\DiagramInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\EventCollectionInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\EventInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\FlowCollectionInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\FlowElementInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\GatewayCollectionInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\GatewayInterface;
use ProcessMaker\Nayra\Contracts\Repositories\RepositoryFactoryInterface;

trait ProcessTrait
{
    use BaseTrait {
        addProperty as baseAddProperty;
    }

    /**
     * @var \Illuminate\Contracts\Events\Dispatcher
     */
    private $dispatcher;

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
}