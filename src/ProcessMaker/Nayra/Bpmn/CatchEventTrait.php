<?php

namespace ProcessMaker\Nayra\Bpmn;

use ProcessMaker\Nayra\Contracts\Bpmn\CatchEventInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\EventDefinitionInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\StateInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\TokenInterface;
use ProcessMaker\Nayra\Contracts\Engine\EngineInterface;
use ProcessMaker\Nayra\Contracts\Engine\ExecutionInstanceInterface;

/**
 * Implementation of the behavior for a catch event.
 *
 * @package ProcessMaker\Nayra\Bpmn
 * @see CatchEventInterface
 */
trait CatchEventTrait
{
    use FlowNodeTrait;

    /**
     * @var \ProcessMaker\Nayra\Contracts\Bpmn\StateInterface[]
     */
    private $triggerPlace = [];

    /**
     * Initialize catch event.
     *
     */
    protected function initCatchEventTrait()
    {
        $this->setProperty(CatchEventInterface::BPMN_PROPERTY_EVENT_DEFINITIONS, new Collection);
        $this->setProperty(CatchEventInterface::BPMN_PROPERTY_PARALLEL_MULTIPLE, false);
    }

    /**
     * Get the event definitions.
     *
     * @return \ProcessMaker\Nayra\Contracts\Bpmn\CollectionInterface
     */
    public function getEventDefinitions()
    {
        return $this->getProperty(CatchEventInterface::BPMN_PROPERTY_EVENT_DEFINITIONS);
    }

    /**
     * Register catch events.
     *
     * @param EngineInterface $engine
     *
     * @return $this
     */
    public function registerCatchEvents(EngineInterface $engine)
    {
        foreach ($this->getEventDefinitions() as $eventDefinition) {
            $eventDefinition->registerWithCatchEvent($engine, $this);
        }
        return $this;
    }

    /**
     * Register catch events.
     *
     * @param TokenInterface|null $token
     *
     * @return $this
     */
    private function activateCatchEvent(TokenInterface $token = null)
    {
        foreach ($this->getEventDefinitions() as $eventDefinition) {
            $eventDefinition->catchEventActivated($this->getOwnerProcess()->getEngine(), $this, $token);
        }
        return $this;
    }

    /**
     * Register the BPMN elements with the engine.
     *
     * @param EngineInterface $engine
     *
     * @return FlowElementInterface
     */
    public function registerWithEngine(EngineInterface $engine)
    {
        $this->registerCatchEvents($engine);
        return $this;
    }

    /**
     * To implement the MessageListener interface
     *
     * @param EventDefinitionInterface $event
     * @param ExecutionInstanceInterface|null $instance
     * @param \ProcessMaker\Nayra\Contracts\Bpmn\TokenInterface|null $token
     *
     * @return $this
     */
    public function execute(EventDefinitionInterface $event, ExecutionInstanceInterface $instance = null, TokenInterface $token = null)
    {
        if ($instance !== null && $this->getActiveState()->getTokens($instance)->count() > 0) {
            foreach ($this->getEventDefinitions() as $index => $eventDefinition) {
                if ($eventDefinition->assertsRule($event, $this, $instance, $token)) {
                    $this->triggerPlace[$index]->addNewToken($instance);
                    $eventDefinition->execute($event, $this, $instance, $token);
                }
            }
        }
        return $this;
    }

    /**
     * Get the active state of the element
     *
     * @return StateInterface
     */
    public function getActiveState()
    {
        return null;
    }

    /**
     * Build events definitions transitions
     *
     * @param string $catchedEventName
     * @param string $consumedEventName
     *
     * @return void
     */
    private function buildEventDefinitionsTransitions($catchedEventName, $consumedEventName)
    {
        $eventDefinitions = $this->getEventDefinitions();
        foreach ($eventDefinitions as $index => $eventDefinition) {
            $triggerPlace = new State($this, CatchEventInterface::TOKEN_STATE_EVENT_CATCH);
            $triggerPlace->connectTo($this->transition);
            $triggerPlace->attachEvent(State::EVENT_TOKEN_ARRIVED, function (TokenInterface $token) use ($catchedEventName) {
                $this->getRepository()
                    ->getTokenRepository()
                    ->persistCatchEventMessageArrives($this, $token);
                $this->notifyEvent($catchedEventName, $this, $token);
            });
            $triggerPlace->attachEvent(State::EVENT_TOKEN_CONSUMED, function (TokenInterface $token) use ($consumedEventName) {
                $this->getRepository()
                    ->getTokenRepository()
                    ->persistCatchEventMessageConsumed($this, $token);
                $this->notifyEvent($consumedEventName, $this, $token);
            });
            $this->triggerPlace[$index] = $triggerPlace;
        }
    }
}
