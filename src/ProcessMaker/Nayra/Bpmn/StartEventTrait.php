<?php

namespace ProcessMaker\Nayra\Bpmn;

use ProcessMaker\Nayra\Contracts\Bpmn\CollectionInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\EventDefinitionInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\EventInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\FlowNodeInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\TokenInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\TransitionInterface;
use ProcessMaker\Nayra\Contracts\Engine\EngineInterface;
use ProcessMaker\Nayra\Contracts\Engine\ExecutionInstanceInterface;
use ProcessMaker\Nayra\Contracts\RepositoryInterface;

/**
 * Implementation of the behavior of a start event.
 *
 * @package ProcessMaker\Nayra\Bpmn
 */
trait StartEventTrait
{

    use CatchEventTrait;
    /**
     *
     * @var StartTransition
     */
    private $transition;

    /**
     * @var \ProcessMaker\Nayra\Contracts\Bpmn\StateInterface
     */
    private $triggerPlace = [];

    /**
     * Build the transitions of the element.
     *
     * @param RepositoryInterface $factory
     */
    public function buildTransitions(RepositoryInterface $factory)
    {
        $this->setRepository($factory);
        $this->transition = new StartTransition($this);
        $this->transition->attachEvent(
            TransitionInterface::EVENT_BEFORE_TRANSIT,
            function(TransitionInterface $transition, CollectionInterface $consumeTokens) {
                $this->notifyEvent(EventInterface::EVENT_EVENT_TRIGGERED, $this, $transition, $consumeTokens);
            }
        );

        foreach($this->getEventDefinitions() as $index => $eventDefinition) {
            $this->triggerPlace[$index] = new State($this, $eventDefinition->getId());
            $this->triggerPlace[$index]->connectTo($this->transition);
        }
    }

    /**
     * Get the input place. Start event does not have an input place.
     *
     * @return null
     */
    public function getInputPlace()
    {
        return null;
    }

    /**
     * Create a flow to a target node.
     *
     * @param \ProcessMaker\Nayra\Contracts\Bpmn\FlowNodeInterface $target
     *
     * @return $this
     */
    protected function buildConnectionTo(FlowNodeInterface $target)
    {
        $this->transition->connectTo($target->getInputPlace());
        return $this;
    }

    /**
     * Start event.
     *
     * @return $this;
     */
    public function start()
    {
        $this->transition->start();
        return $this;
    }

    /**
     * Method to be called when a message event arrives
     *
     * @param EventDefinitionInterface $event
     * @param ExecutionInstanceInterface|null $instance
     * @param TokenInterface|null $token
     *
     * @return $this
     */
    public function execute(EventDefinitionInterface $event, ExecutionInstanceInterface $instance = null, TokenInterface $token = null)
    {
        $start = $this->getEventDefinitions()->count() === 0;
        $index = -1;
        foreach ($this->getEventDefinitions() as $index => $eventDefinition) {
            if ($eventDefinition->assertsRule($event, $this, $instance)) {
                $start = true;
                break;
            }
        }
        if ($start) {
            if ($instance === null) {
                $process = $this->getOwnerProcess();
                $dataStorage = $process->getRepository()->createDataStore();
                $instance = $process->getEngine()->createExecutionInstance($process, $dataStorage);
            }
            //Execute the behavior of the EventDefinition
            foreach ($this->getEventDefinitions() as $index => $eventDefinition) {
                $eventDefinition->execute($event, $this, $instance, $token);
            }
            $this->start();
            // with a new token in the trigger place, the event catch element will be fired
            $index < 0 ?: $this->triggerPlace[$index]->addNewToken($instance);
        }
        return $this;
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
            if (is_callable([$eventDefinition, 'registerCatchEvents'])) {
                $eventDefinition->registerCatchEvents($engine, $this, null);
            }
        }
        return $this;
    }
}
