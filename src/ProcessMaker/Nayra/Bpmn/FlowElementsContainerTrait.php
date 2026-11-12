<?php

namespace ProcessMaker\Nayra\Bpmn;

use ProcessMaker\Nayra\Contracts\Bpmn\ActivityInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\EventInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\FlowElementInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\FlowElementsContainerInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\FlowNodeInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\GatewayInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\ProcessInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\StartEventInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\SubProcessInterface;
use ProcessMaker\Nayra\Contracts\RepositoryInterface;

/**
 * Shared container behavior for SubProcess (and similar) containers.
 */
trait FlowElementsContainerTrait
{
    /**
     * Initialize container collections.
     */
    protected function initFlowElementsContainerTrait()
    {
        $this->setProperty('activities', new Collection);
        $this->setProperty('events', new Collection);
        $this->setProperty('gateways', new Collection);
        $this->setProperty('artifacts', new Collection);
        $this->setProperty(ProcessInterface::BPMN_PROPERTY_LANE_SET, new Collection);
    }

    /**
     * @return Collection
     */
    public function getActivities()
    {
        return $this->getProperty('activities');
    }

    /**
     * @return Collection
     */
    public function getEvents()
    {
        return $this->getProperty('events');
    }

    /**
     * @return Collection
     */
    public function getGateways()
    {
        return $this->getProperty('gateways');
    }

    /**
     * @return Collection
     */
    public function getArtifacts()
    {
        return $this->getProperty('artifacts');
    }

    /**
     * @return Collection
     */
    public function getLaneSets()
    {
        return $this->getProperty(ProcessInterface::BPMN_PROPERTY_LANE_SET);
    }

    /**
     * @return Collection
     */
    public function getFlowElements()
    {
        $elements = [];
        foreach ($this->getActivities() as $activity) {
            $elements[] = $activity;
        }
        foreach ($this->getEvents() as $event) {
            $elements[] = $event;
        }
        foreach ($this->getGateways() as $gateway) {
            $elements[] = $gateway;
        }
        foreach ($this->getArtifacts() as $artifact) {
            if ($artifact instanceof FlowElementInterface) {
                $elements[] = $artifact;
            }
        }

        return new Collection($elements);
    }

    /**
     * @param FlowElementInterface $element
     *
     * @return $this
     */
    public function addFlowElement(FlowElementInterface $element)
    {
        if (method_exists($element, 'setContainer')) {
            $element->setContainer($this);
        }
        if ($this instanceof ProcessInterface === false
            && method_exists($this, 'getOwnerProcess')
            && $this->getOwnerProcess()) {
            $element->setOwnerProcess($this->getOwnerProcess());
            if ($element instanceof FlowNodeInterface && method_exists($element, 'setProcess')) {
                $element->setProcess($this->getOwnerProcess());
            }
        }
        if ($element instanceof ActivityInterface) {
            $this->getActivities()->push($element);
        } elseif ($element instanceof EventInterface) {
            $this->getEvents()->push($element);
        } elseif ($element instanceof GatewayInterface) {
            $this->getGateways()->push($element);
        } else {
            $this->getArtifacts()->push($element);
        }

        return $this;
    }

    /**
     * Build transitions for every nested flow node, including nested subprocesses.
     *
     * @param RepositoryInterface $factory
     * @param array $transitions
     *
     * @return array
     */
    protected function buildContainerTransitions(RepositoryInterface $factory, array &$transitions = [])
    {
        foreach ($this->getEvents() as $event) {
            $event->buildTransitions($factory);
        }
        foreach ($this->getActivities() as $activity) {
            $activity->buildTransitions($factory);
        }
        foreach ($this->getGateways() as $gateway) {
            $gateway->buildTransitions($factory);
        }

        foreach ($this->getEvents() as $event) {
            $event->buildFlowTransitions($factory);
        }
        foreach ($this->getActivities() as $activity) {
            $activity->buildFlowTransitions($factory);
        }
        foreach ($this->getGateways() as $gateway) {
            $gateway->buildFlowTransitions($factory);
        }

        foreach ($this->getEvents() as $event) {
            $transitions = array_merge($transitions, $event->getTransitions());
        }
        foreach ($this->getActivities() as $activity) {
            $transitions = array_merge($transitions, $activity->getTransitions());
            if ($activity instanceof SubProcessInterface) {
                $activity->buildInnerTransitions($factory, $transitions);
            }
        }
        foreach ($this->getGateways() as $gateway) {
            $transitions = array_merge($transitions, $gateway->getTransitions());
        }

        return $transitions;
    }

    /**
     * Find none start events in this container.
     *
     * @return StartEventInterface[]
     */
    public function findStartEvents()
    {
        $starts = [];
        foreach ($this->getEvents() as $event) {
            if ($event instanceof StartEventInterface && $event->getEventDefinitions()->count() === 0) {
                $starts[] = $event;
            }
        }

        return $starts;
    }

    /**
     * Structural validation for a subprocess container.
     *
     * @param SubProcessInterface $subProcess
     *
     * @return string[]
     */
    protected function validateSubProcessRules(SubProcessInterface $subProcess)
    {
        $errors = [];
        if (!$subProcess->getId()) {
            $errors[] = 'SubProcess identifier is required';
        }
        foreach ($subProcess->getFlowElements() as $element) {
            if (method_exists($element, 'getContainer') && $element->getContainer() !== $subProcess) {
                $errors[] = sprintf(
                    'Flow element %s does not belong to subprocess %s',
                    $element->getId(),
                    $subProcess->getId()
                );
            }
        }
        foreach ([$subProcess->getActivities(), $subProcess->getEvents(), $subProcess->getGateways()] as $nodes) {
            foreach ($nodes as $node) {
                foreach ($node->getOutgoingFlows() as $flow) {
                    $target = $flow->getTarget();
                    if ($target && method_exists($target, 'getContainer') && $target->getContainer() !== $subProcess) {
                        $errors[] = sprintf(
                            'Sequence flow from %s leaves subprocess %s',
                            $node->getId(),
                            $subProcess->getId()
                        );
                    }
                }
            }
        }
        foreach ($subProcess->getEvents() as $event) {
            if ($event instanceof StartEventInterface && method_exists($event, 'getContainer')
                && $event->getContainer() !== $subProcess) {
                $errors[] = sprintf(
                    'Start event %s does not belong to subprocess %s',
                    $event->getId(),
                    $subProcess->getId()
                );
            }
        }
        if (!$subProcess->getTriggeredByEvent()) {
            $hasStart = false;
            foreach ($subProcess->getEvents() as $event) {
                if ($event instanceof StartEventInterface) {
                    $hasStart = true;
                    break;
                }
            }
            if (!$hasStart) {
                $errors[] = sprintf(
                    'Embedded subprocess %s requires at least one start event',
                    $subProcess->getId()
                );
            }
        }
        if ($this->hasCyclicContainment($subProcess, [])) {
            $errors[] = sprintf('SubProcess %s has a cyclic parent relationship', $subProcess->getId());
        }

        return $errors;
    }

    /**
     * @param FlowElementsContainerInterface $container
     * @param array $visited
     *
     * @return bool
     */
    private function hasCyclicContainment(FlowElementsContainerInterface $container, array $visited)
    {
        $id = method_exists($container, 'getId') ? $container->getId() : spl_object_hash($container);
        if (isset($visited[$id])) {
            return true;
        }
        $visited[$id] = true;
        foreach ($container->getActivities() as $activity) {
            if ($activity instanceof FlowElementsContainerInterface
                && $this->hasCyclicContainment($activity, $visited)) {
                return true;
            }
        }

        return false;
    }
}
