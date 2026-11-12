<?php

namespace ProcessMaker\Nayra\Bpmn;

use ProcessMaker\Nayra\Bpmn\Models\ScopeExecution;
use ProcessMaker\Nayra\Contracts\Bpmn\ActivityInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\AdHocSubProcessInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\CancelEventDefinitionInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\EndEventInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\EventInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\FlowElementsContainerInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\SubProcessInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\TokenInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\TransactionInterface;
use ProcessMaker\Nayra\Contracts\Engine\ExecutionInstanceInterface;
use ProcessMaker\Nayra\Contracts\RepositoryInterface;

/**
 * Runtime behavior for an embedded SubProcess (same process instance, child scope).
 */
trait EmbeddedSubProcessTrait
{
    use ActivityTrait;
    use FlowElementsContainerTrait {
        FlowElementsContainerTrait::initFlowElementsContainerTrait as private initContainerCollections;
    }

    /**
     * @var array
     */
    private $innerTransitions = [];

    /**
     * @var bool
     */
    private $innerBuilt = false;

    /**
     * Initialize collections and activation hook.
     */
    protected function initEmbeddedSubProcess()
    {
        $this->initContainerCollections();
        $this->setTriggeredByEvent(false);
        $this->attachEvent(
            ActivityInterface::EVENT_ACTIVITY_ACTIVATED,
            function ($self, TokenInterface $token) {
                $this->onEmbeddedSubProcessActivated($token);
            }
        );
    }

    /**
     * Activation hook: event subprocesses are started by their start events, not by enterSubProcess.
     *
     * @param TokenInterface $token
     *
     * @return void
     */
    protected function onEmbeddedSubProcessActivated(TokenInterface $token)
    {
        if ($this->getTriggeredByEvent()) {
            return;
        }
        $this->enterSubProcess($token);
    }

    /**
     * @return bool
     */
    public function getTriggeredByEvent()
    {
        return (bool) $this->getProperty(SubProcessInterface::BPMN_PROPERTY_TRIGGERED_BY_EVENT, false);
    }

    /**
     * @param bool $triggeredByEvent
     *
     * @return $this
     */
    public function setTriggeredByEvent($triggeredByEvent)
    {
        $this->setProperty(SubProcessInterface::BPMN_PROPERTY_TRIGGERED_BY_EVENT, (bool) $triggeredByEvent);

        return $this;
    }

    /**
     * Build transitions of nested flow nodes.
     *
     * @param RepositoryInterface $factory
     * @param array $transitions
     *
     * @return array
     */
    public function buildInnerTransitions(RepositoryInterface $factory, array &$transitions = [])
    {
        if ($this->innerBuilt) {
            $transitions = array_merge($transitions, $this->innerTransitions);

            return $this->innerTransitions;
        }
        $this->innerTransitions = [];
        $this->buildContainerTransitions($factory, $this->innerTransitions);
        $this->innerBuilt = true;
        $transitions = array_merge($transitions, $this->innerTransitions);

        return $this->innerTransitions;
    }

    /**
     * Enter the subprocess: create a child scope and start inner none starts.
     *
     * @param TokenInterface $token
     *
     * @return void
     */
    protected function enterSubProcess(TokenInterface $token)
    {
        $instance = $token->getInstance();
        $scope = new ScopeExecution($token, $this, $instance);
        $token->setProperty('subprocessScope', $scope);
        $token->setProperty('subprocessWaiting', true);

        if ($this instanceof AdHocSubProcessInterface) {
            $this->enterAdHocSubProcess($token, $scope, $instance);

            return;
        }

        foreach ($this->findStartEvents() as $startEvent) {
            $startEvent->start($instance);
        }

        $this->attachScopeCompletionListeners($token, $scope, $instance);
    }

    /**
     * Ad-hoc entry: activate activities without forcing a single sequence.
     *
     * @param TokenInterface $token
     * @param ScopeExecution $scope
     * @param ExecutionInstanceInterface $instance
     *
     * @return void
     */
    protected function enterAdHocSubProcess(TokenInterface $token, ScopeExecution $scope, ExecutionInstanceInterface $instance)
    {
        $ordering = $this->getOrdering();
        $activities = $this->getActivities();
        if ($ordering === AdHocSubProcessInterface::ORDERING_SEQUENTIAL) {
            if ($activities->count() > 0) {
                $this->activateAdHocActivity($activities->item(0), $instance);
            }
        } else {
            foreach ($activities as $activity) {
                $this->activateAdHocActivity($activity, $instance);
            }
        }
        $this->attachScopeCompletionListeners($token, $scope, $instance, true);
    }

    /**
     * @param ActivityInterface $activity
     * @param ExecutionInstanceInterface $instance
     *
     * @return void
     */
    private function activateAdHocActivity(ActivityInterface $activity, ExecutionInstanceInterface $instance)
    {
        if ($activity instanceof FlowElementsContainerInterface) {
            return;
        }
        foreach ($activity->getStates() as $state) {
            if ($state->getName() === ActivityInterface::TOKEN_STATE_ACTIVE) {
                $state->addNewToken($instance);

                return;
            }
        }
    }

    /**
     * Listen for end/cancel events to complete or cancel the parent token.
     *
     * @param TokenInterface $token
     * @param ScopeExecution $scope
     * @param ExecutionInstanceInterface $instance
     * @param bool $adHoc
     *
     * @return void
     */
    protected function attachScopeCompletionListeners(
        TokenInterface $token,
        ScopeExecution $scope,
        ExecutionInstanceInterface $instance,
        $adHoc = false
    ) {
        $process = $this->getOwnerProcess();
        $check = function () use ($token, $scope, $instance, $adHoc) {
            $this->checkEmbeddedSubProcessCompletion($token, $scope, $instance, $adHoc);
        };

        $process->attachEvent(
            EventInterface::EVENT_EVENT_TRIGGERED,
            function ($element) use ($token, $scope, $instance, $check) {
                if (!$scope->isActive() || $scope->isCancelled() || !$this->containsNode($element)) {
                    return;
                }
                if ($element instanceof EndEventInterface) {
                    foreach ($element->getEventDefinitions() as $definition) {
                        if ($definition instanceof CancelEventDefinitionInterface) {
                            $this->cancelTransaction($token, $scope, $instance);

                            return;
                        }
                    }
                }
                $this->getOwnerProcess()->getEngine()->nextState($check);
            }
        );
        // Defer completion checks so tokens in transit to the next node are visible.
        $deferredCheck = function () use ($check) {
            $this->getOwnerProcess()->getEngine()->nextState($check);
        };
        $process->attachEvent(ActivityInterface::EVENT_ACTIVITY_CLOSED, $deferredCheck);
        $process->attachEvent(ActivityInterface::EVENT_ACTIVITY_COMPLETED, $deferredCheck);
    }

    /**
     * Decide whether the parent subprocess token can complete.
     *
     * @param TokenInterface $token
     * @param ScopeExecution $scope
     * @param ExecutionInstanceInterface $instance
     * @param bool $adHoc
     *
     * @return void
     */
    protected function checkEmbeddedSubProcessCompletion(
        TokenInterface $token,
        ScopeExecution $scope,
        ExecutionInstanceInterface $instance,
        $adHoc = false
    ) {
        if (!$scope->isActive() || $scope->isCancelled()) {
            return;
        }
        if ($token->getStatus() !== ActivityInterface::TOKEN_STATE_ACTIVE) {
            return;
        }
        if ($adHoc && $this instanceof AdHocSubProcessInterface) {
            $condition = $this->getCompletionCondition();
            if ($condition) {
                $data = $instance->getDataStore()->getData();
                if ($condition($data)) {
                    $this->completeEmbeddedSubProcess($token, $scope);

                    return;
                }
            }
        }
        if ($scope->getActiveTokensCount() === 0) {
            $this->completeEmbeddedSubProcess($token, $scope);
        }
    }

    /**
     * @param TokenInterface $token
     * @param ScopeExecution $scope
     *
     * @return void
     */
    protected function completeEmbeddedSubProcess(TokenInterface $token, ScopeExecution $scope)
    {
        if (!$scope->isActive()) {
            return;
        }
        $scope->destroy();
        $token->setProperty('subprocessWaiting', false);
        $token->setStatus(ActivityInterface::TOKEN_STATE_COMPLETED);
    }

    /**
     * Cancel a transaction: activate compensation activities and do not follow success flows.
     *
     * @param TokenInterface $token
     * @param ScopeExecution $scope
     * @param ExecutionInstanceInterface $instance
     *
     * @return void
     */
    protected function cancelTransaction(TokenInterface $token, ScopeExecution $scope, ExecutionInstanceInterface $instance)
    {
        if (!$scope->isActive()) {
            return;
        }
        foreach ($this->getActivities() as $activity) {
            if (!$activity->getProperty('isForCompensation')) {
                continue;
            }
            if (count($activity->getStates()) === 0) {
                $activity->buildTransitions($this->getRepository());
            }
            foreach ($activity->getStates() as $state) {
                if ($state->getName() === ActivityInterface::TOKEN_STATE_ACTIVE) {
                    $state->addNewToken($instance);
                    break;
                }
            }
        }
        $scope->markCancelled();
        $scope->destroy();
        $token->setProperty('subprocessWaiting', false);
        // Close without taking outgoing success flows (token never reaches COMPLETED).
        $token->setStatus(ActivityInterface::TOKEN_STATE_CLOSED);
        $this->getRepository()->getTokenRepository()->persistActivityClosed($this, $token);
    }

    /**
     * @param mixed $node
     *
     * @return bool
     */
    private function containsNode($node)
    {
        foreach ($this->getEvents() as $event) {
            if ($event === $node) {
                return true;
            }
        }
        foreach ($this->getActivities() as $activity) {
            if ($activity === $node) {
                return true;
            }
            if ($activity instanceof FlowElementsContainerInterface
                && method_exists($activity, 'containsNode')
                && $activity->containsNode($node)) {
                return true;
            }
            if ($activity instanceof FlowElementsContainerInterface) {
                foreach ($activity->getEvents() as $event) {
                    if ($event === $node) {
                        return true;
                    }
                }
                foreach ($activity->getActivities() as $inner) {
                    if ($inner === $node) {
                        return true;
                    }
                }
                foreach ($activity->getGateways() as $gateway) {
                    if ($gateway === $node) {
                        return true;
                    }
                }
            }
        }
        foreach ($this->getGateways() as $gateway) {
            if ($gateway === $node) {
                return true;
            }
        }
        if (method_exists($node, 'getContainer')) {
            $container = $node->getContainer();
            while ($container) {
                if ($container === $this) {
                    return true;
                }
                $container = method_exists($container, 'getContainer') ? $container->getContainer() : null;
            }
        }

        return false;
    }

    /**
     * @return string[]
     */
    public function validate()
    {
        return $this->validateSubProcessRules($this);
    }
}
