<?php

namespace ProcessMaker\Nayra\Bpmn;

use Illuminate\Contracts\Events\Dispatcher;
use ProcessMaker\Nayra\Contracts\Bpmn\ActivityInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\CollectionInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\EventInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\GatewayInterface;
use ProcessMaker\Nayra\Contracts\Repositories\RepositoryFactoryInterface;

trait ProcessTrait
{
    use BaseTrait;

    /**
     * @var \ProcessMaker\Nayra\Contracts\Bpmn\ActivityCollectionInterface $activities
     */
    protected $activities;
    /**
     * @var \ProcessMaker\Nayra\Contracts\Bpmn\EventCollectionInterface $events
     */
    protected $events;
    /**
     * @var \ProcessMaker\Nayra\Contracts\Bpmn\GatewayCollectionInterface $gateways
     */
    protected $gateways;

    protected $dataStores;
    protected $artifacts;
    protected $flows;
    protected $diagram;

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
        return $this->activities;
    }

    public function getDataStores()
    {
        return $this->dataStores;
    }

    public function getArtifacts()
    {
        return $this->artifacts;
    }

    public function getDiagram()
    {
        return $this->diagram;
    }

    public function getEvents()
    {
        return $this->events;
    }

    public function getFlows()
    {
        return $this->flows;
    }

    public function getGateways()
    {
        return $this->gateways;
    }

    public function setActivities(\ProcessMaker\Nayra\Contracts\Bpmn\ActivityCollectionInterface $activities)
    {
        $this->activities = $activities;
        return $this;
    }

    public function setDataStores(\ProcessMaker\Nayra\Contracts\Bpmn\DataStoreCollectionInterface $dataStores)
    {
        $this->dataStores = $dataStores;
        return $this;
    }

    public function setArtifacts(\ProcessMaker\Nayra\Contracts\Bpmn\ArtifactCollectionInterface $artifacts)
    {
        $this->artifacts = $artifacts;
        return $this;
    }

    public function setDiagram(\ProcessMaker\Nayra\Contracts\Bpmn\DiagramInterface $diagram)
    {
        $this->diagram = $diagram;
        return $this;
    }

    public function setEvents(\ProcessMaker\Nayra\Contracts\Bpmn\EventCollectionInterface $events)
    {
        $this->events = $events;
        return $this;
    }

    public function setFlows(\ProcessMaker\Nayra\Contracts\Bpmn\FlowCollectionInterface $flows)
    {
        $this->flows = $flows;
        return $this;
    }

    public function setGateways(\ProcessMaker\Nayra\Contracts\Bpmn\GatewayCollectionInterface $gateways)
    {
        $this->gateways = $gateways;
        return $this;
    }

    /**
     * @return CollectionInterface
     */
    public function getTransitions(RepositoryFactoryInterface $factory)
    {
        //Build the runtime elements
        foreach($this->events as $event) {
            $event->buildTransitions($factory);
        }
        foreach($this->activities as $activity) {
            $activity->buildTransitions($factory);
        }
        foreach($this->gateways as $gateway) {
            $gateway->buildTransitions($factory);
        }
        //Build the runtime flows
        foreach($this->events as $event) {
            $event->buildFlowTransitions($factory);
        }
        foreach($this->activities as $activity) {
            $activity->buildFlowTransitions($factory);
        }
        foreach($this->gateways as $gateway) {
            $gateway->buildFlowTransitions($factory);
        }
        //Get the transitions
        $transitions = [];
        foreach($this->events as $event) {
            $transitions = array_merge($transitions, $event->getTransitions());
        }
        foreach($this->activities as $activity) {
            $transitions = array_merge($transitions, $activity->getTransitions());
        }
        foreach($this->gateways as $gateway) {
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
        //$activity->registerTransitions($this);
        $this->activities->push($activity);
        return $this;
    }

    /**
     * @return $this
     */
    public function addEvent(EventInterface $event)
    {
        $event->setOwnerProcess($this);
        $this->events->push($event);
        return $this;
    }

    /**
     * @return $this
     */
    public function addGateway(GatewayInterface $gateway)
    {
        $gateway->setOwnerProcess($this);
        $this->gateways->push($gateway);
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