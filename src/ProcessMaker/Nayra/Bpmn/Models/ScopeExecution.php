<?php

namespace ProcessMaker\Nayra\Bpmn\Models;

use ProcessMaker\Nayra\Contracts\Bpmn\FlowElementsContainerInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\FlowNodeInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\TokenInterface;
use ProcessMaker\Nayra\Contracts\Engine\ExecutionInstanceInterface;

/**
 * Child execution scope owned by an embedded SubProcess within a parent instance.
 */
class ScopeExecution
{
    /**
     * @var string
     */
    private $id;

    /**
     * @var TokenInterface
     */
    private $parentToken;

    /**
     * @var FlowElementsContainerInterface
     */
    private $container;

    /**
     * @var ExecutionInstanceInterface
     */
    private $instance;

    /**
     * @var bool
     */
    private $active = true;

    /**
     * @var bool
     */
    private $cancelled = false;

    public function __construct(
        TokenInterface $parentToken,
        FlowElementsContainerInterface $container,
        ExecutionInstanceInterface $instance
    ) {
        $this->id = uniqid('scope_', true);
        $this->parentToken = $parentToken;
        $this->container = $container;
        $this->instance = $instance;
    }

    /**
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return TokenInterface
     */
    public function getParentToken()
    {
        return $this->parentToken;
    }

    /**
     * @return FlowElementsContainerInterface
     */
    public function getContainer()
    {
        return $this->container;
    }

    /**
     * @return ExecutionInstanceInterface
     */
    public function getInstance()
    {
        return $this->instance;
    }

    /**
     * @return ScopeExecution|null
     */
    public function getParentScope()
    {
        $container = $this->container;
        if (!method_exists($container, 'getContainer')) {
            return null;
        }
        $parentContainer = $container->getContainer();
        if (!$parentContainer instanceof FlowElementsContainerInterface) {
            return null;
        }
        if (!method_exists($parentContainer, 'getTokens')) {
            return null;
        }
        $tokens = $parentContainer->getTokens($this->instance);
        if ($tokens->count() === 0) {
            return null;
        }
        $scope = $tokens->item(0)->getProperty('subprocessScope');

        return $scope instanceof self ? $scope : null;
    }

    /**
     * Count active tokens currently located on flow nodes inside this container.
     *
     * @return int
     */
    public function getActiveTokensCount()
    {
        $count = 0;
        foreach ($this->getInnerFlowNodes() as $node) {
            if ($node === $this->container) {
                continue;
            }
            $count += $node->getTokens($this->instance)->count();
        }

        return $count;
    }

    /**
     * @return bool
     */
    public function isActive()
    {
        return $this->active;
    }

    /**
     * Mark this scope as cancelled (transaction cancel).
     *
     * @return void
     */
    public function markCancelled()
    {
        $this->cancelled = true;
    }

    /**
     * @return bool
     */
    public function isCancelled()
    {
        return $this->cancelled;
    }

    /**
     * Destroy this scope.
     *
     * @return void
     */
    public function destroy()
    {
        $this->active = false;
        $this->parentToken->setProperty('subprocessScope', null);
    }

    /**
     * @return FlowNodeInterface[]
     */
    private function getInnerFlowNodes()
    {
        $nodes = [];
        foreach ($this->container->getActivities() as $activity) {
            $nodes[] = $activity;
            if ($activity instanceof FlowElementsContainerInterface && $activity !== $this->container) {
                $nodes = array_merge($nodes, $this->collectFromContainer($activity));
            }
        }
        foreach ($this->container->getEvents() as $event) {
            $nodes[] = $event;
        }
        foreach ($this->container->getGateways() as $gateway) {
            $nodes[] = $gateway;
        }

        return $nodes;
    }

    /**
     * @param FlowElementsContainerInterface $container
     *
     * @return FlowNodeInterface[]
     */
    private function collectFromContainer(FlowElementsContainerInterface $container)
    {
        $nodes = [];
        foreach ($container->getActivities() as $activity) {
            $nodes[] = $activity;
            if ($activity instanceof FlowElementsContainerInterface && $activity !== $container) {
                $nodes = array_merge($nodes, $this->collectFromContainer($activity));
            }
        }
        foreach ($container->getEvents() as $event) {
            $nodes[] = $event;
        }
        foreach ($container->getGateways() as $gateway) {
            $nodes[] = $gateway;
        }

        return $nodes;
    }
}
