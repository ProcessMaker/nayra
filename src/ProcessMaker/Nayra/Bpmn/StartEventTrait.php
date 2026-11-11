<?php

namespace ProcessMaker\Nayra\Bpmn;

use ProcessMaker\Nayra\Contracts\Bpmn\CatchEventInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\CollectionInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\EndEventInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\EventDefinitionInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\EventInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\FlowInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\MessageEventDefinitionInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\SubProcessInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\ThrowEventInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\TokenInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\TransitionInterface;
use ProcessMaker\Nayra\Contracts\Engine\EngineInterface;
use ProcessMaker\Nayra\Contracts\Engine\ExecutionInstanceInterface;
use ProcessMaker\Nayra\Contracts\RepositoryInterface;

/**
 * Implementation of the behavior of a start event.
 */
trait StartEventTrait
{
    use CatchEventTrait;

    /**
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
        $this->transition = new Transition($this);
        $this->transition->attachEvent(
            TransitionInterface::EVENT_BEFORE_TRANSIT,
            function (TransitionInterface $transition, CollectionInterface $consumeTokens) {
                $this->getRepository()
                    ->getTokenRepository()
                    ->persistStartEventTriggered($this, $consumeTokens);
                $this->notifyEvent(EventInterface::EVENT_EVENT_TRIGGERED, $this, $transition, $consumeTokens);
            }
        );

        $eventDefinitions = $this->getEventDefinitions();
        foreach ($eventDefinitions as $index => $eventDefinition) {
            $this->triggerPlace[$index] = new State($this, $eventDefinition->getId());
            $this->triggerPlace[$index]->connectTo($this->transition);
        }
        if ($eventDefinitions->count() === 0) {
            $this->triggerPlace[0] = new State($this);
            $this->triggerPlace[0]->connectTo($this->transition);
        }
    }

    /**
     * Get the input place. Start event does not have an input place.
     *
     * @param FlowInterface|null $targetFlow
     *
     * @return null
     */
    public function getInputPlace(FlowInterface $targetFlow = null)
    {
        return null;
    }

    /**
     * Create a flow to a target node.
     *
     * @param \ProcessMaker\Nayra\Contracts\Bpmn\FlowInterface $targetFlow
     *
     * @return $this
     */
    protected function buildConnectionTo(FlowInterface $targetFlow)
    {
        $this->transition->connectTo($targetFlow->getTarget()->getInputPlace($targetFlow));

        return $this;
    }

    /**
     * Start event.
     *
     * @param \ProcessMaker\Nayra\Contracts\Engine\ExecutionInstanceInterface $instance
     *
     * @return $this;
     */
    public function start(ExecutionInstanceInterface $instance)
    {
        $this->triggerPlace[0]->addNewToken($instance);

        return $this;
    }

    /**
     * Method to be called when a message event arrives
     *
     * @param EventDefinitionInterface $eventDef
     * @param ExecutionInstanceInterface|null $instance
     * @param TokenInterface|null $token
     *
     * @return $this
     */
    public function execute(EventDefinitionInterface $eventDef, ExecutionInstanceInterface $instance = null, TokenInterface $token = null)
    {
        $container = $this->getContainer();
        if ($container instanceof SubProcessInterface && $container->getTriggeredByEvent()) {
            return $this->executeEventSubProcessStart($eventDef, $instance, $token);
        }
        foreach ($this->getEventDefinitions() as $index => $eventDefinition) {
            if ($eventDefinition->assertsRule($eventDef, $this, $instance, $token)) {
                if ($instance === null) {
                    $process = $this->getOwnerProcess();
                    $data = $eventDefinition->getPayloadData($token, $this);
                    $dataStorage = $process->getRepository()->createDataStore();
                    $sourceHasDataMapping = $token?->getOwnerElement()?->getDataInputs()?->count();
                    $targetHasDataMapping = $this->getDataOutputAssociations()?->count();
                    if (!$sourceHasDataMapping && !$targetHasDataMapping) {
                        $dataStorage->setData($data);
                    }
                    $instance = $process->getEngine()->createExecutionInstance($process, $dataStorage);
                }
                $this->triggerPlace[$index]->addNewToken($instance);
                $eventDefinition->execute($eventDef, $this, $instance, $token);
            }
        }

        return $this;
    }

    /**
     * Event subprocess starts inside an existing parent process instance.
     *
     * @param EventDefinitionInterface $eventDef
     * @param ExecutionInstanceInterface|null $instance
     * @param TokenInterface|null $token
     *
     * @return $this
     */
    private function executeEventSubProcessStart(
        EventDefinitionInterface $eventDef,
        ExecutionInstanceInterface $instance = null,
        TokenInterface $token = null
    ) {
        $targets = [];
        if ($instance !== null) {
            if ($instance->getTokens()->count() > 0) {
                $targets[] = $instance;
            }
        } else {
            foreach ($this->getOwnerProcess()->getInstances() as $activeInstance) {
                if ($activeInstance->getTokens()->count() > 0) {
                    $targets[] = $activeInstance;
                }
            }
        }
        if (count($targets) === 0) {
            return $this;
        }
        foreach ($targets as $targetInstance) {
            foreach ($this->getEventDefinitions() as $index => $eventDefinition) {
                if ($eventDefinition->assertsRule($eventDef, $this, $targetInstance, $token)) {
                    $this->triggerPlace[$index]->addNewToken($targetInstance);
                    $eventDefinition->execute($eventDef, $this, $targetInstance, $token);
                }
            }
        }

        return $this;
    }

    /**
     * Whether this event-subprocess start is still subscribed for a parent instance.
     *
     * @param ExecutionInstanceInterface $instance
     *
     * @return bool
     */
    public function isSubscribedForInstance(ExecutionInstanceInterface $instance)
    {
        $container = $this->getContainer();
        if (!($container instanceof SubProcessInterface) || !$container->getTriggeredByEvent()) {
            return true;
        }

        return $instance->getTokens()->count() > 0;
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
        $this->activateCatchEvent(null);

        return $this;
    }
}
