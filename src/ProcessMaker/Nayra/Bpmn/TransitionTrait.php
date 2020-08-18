<?php

namespace ProcessMaker\Nayra\Bpmn;

use ProcessMaker\Nayra\Bpmn\Models\Flow;
use ProcessMaker\Nayra\Contracts\Bpmn\CollectionInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\ConnectionInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\FlowElementInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\FlowNodeInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\TokenInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\TransitionInterface;
use ProcessMaker\Nayra\Contracts\Engine\ExecutionInstanceInterface;

/**
 * Control the transition of tokens through states.
 *
 */
trait TransitionTrait
{
    use FlowElementTrait,
        TraversableTrait,
        ObservableTrait;

    /**
     * Flow node owner of the transition.
     *
     * @var FlowNodeInterface $owner
     */
    protected $owner;

    /**
     * How many tokens are consumed per transition.
     * (0 or -1 = Means no limit)
     *
     * @var int $tokensConsumedPerTransition
     */
    private $tokensConsumedPerTransition = -1;

    /**
     * How many tokens are consumed per incoming.
     * (0 or -1 = Means no limit)
     *
     * @var int $tokensConsumedPerIncoming
     */
    private $tokensConsumedPerIncoming = 1;

    /**
     * @var boolean
     */
    private $preserveToken = false;

    /**
     * Initialize the transition.
     *
     * @param FlowNodeInterface $owner
     * @param bool $preserveToken
     */
    protected function initTransition(FlowNodeInterface $owner, $preserveToken = false)
    {
        $this->owner = $owner;
        $owner->addTransition($this);
        $this->setPreserveToken($preserveToken);
    }

    /**
     * Evaluate true if all the incoming has at least one token.
     *
     * @param ExecutionInstanceInterface $instance
     *
     * @return boolean
     */
    protected function hasAllRequiredTokens(ExecutionInstanceInterface $instance)
    {
        return $this->incoming()->count() > 0 && $this->incoming()->find(function ($flow) use ($instance) {
            return $flow->origin()->getTokens($instance)->count() === 0;
        })->count() === 0;
    }

    /**
     * Action executed when the transition condition evaluates to false.
     *
     * By default a transition does not do any action if the condition is false.
     *
     * @return boolean
     */
    protected function conditionIsFalse()
    {
        return false;
    }

    /**
     * Do the transition of the selected tokens.
     *
     * @param CollectionInterface $consumeTokens
     * @param \ProcessMaker\Nayra\Contracts\Engine\ExecutionInstanceInterface $executionInstance
     *
     * @return boolean
     */
    protected function doTransit(CollectionInterface $consumeTokens, ExecutionInstanceInterface $executionInstance)
    {
        if (get_class($this) === ConditionedExclusiveTransition::class && !empty($this->getOwner()->getOwnerDocument())) {
            $source = $this->outgoing()->item(0)->origin()->getOwner();
            $target = $this->outgoing->item(0)->target()->getOwner();
            $flow = $this->findFlow($source, $target);
            $this->notifyConditionedTransition(TransitionInterface::EVENT_CONDITIONED_TRANSITION, $this, $flow, $executionInstance);
        }

        $this->notifyEvent(TransitionInterface::EVENT_BEFORE_TRANSIT, $this, $consumeTokens);
        $consumedTokensCount = $consumeTokens->count();
        $consumeTokens->find(function (TokenInterface $token) use ($executionInstance) {
            $token->getOwner()->consumeToken($token, $executionInstance);
        });

        $this->notifyEvent(TransitionInterface::EVENT_AFTER_CONSUME, $this, $consumeTokens);

        $this->outgoing()->find(function (ConnectionInterface $flow) use ($consumeTokens, $executionInstance, $consumedTokensCount) {
            if ($this->preserveToken && $consumedTokensCount == 1) {
                $consumeTokens->find(function (TokenInterface $token) use ($flow, $executionInstance) {
                    $flow->targetState()->addToken($executionInstance, $token, false, $this);
                });
            } else {
                $flow->targetState()->addNewToken($executionInstance, [], $this);
            }
        });

        $this->notifyEvent(TransitionInterface::EVENT_AFTER_TRANSIT, $this, $consumeTokens);

        return true;
    }

    /**
     * Find the Node's flow that is traversed
     * @param $origin
     * @param $target
     * @return mixed
     * @throws \ErrorException
     */
    private function findFlow($origin, $target)
    {
        $bpmnElements = $this->getOwner()->getOwnerDocument()->getBpmnElements();
        $matchingFlows = array_values(array_filter($bpmnElements, function ($element) use ($origin, $target) {
            return get_class($element) === Flow::class
                && $element->getSource()->getId() === $origin->getId()
                && $element->getTarget()->getId() === $target->getId();
        }));

        if (count($matchingFlows) == 0) { throw new \ErrorException("The flow can't be found within the bpmn"); }

        return $matchingFlows[0];
    }

    /**
     * Notify in the bus that a conditioned transition has been activated
     *
     * @param $event
     * @param mixed ...$arguments
     */
    protected function notifyConditionedTransition($event, ...$arguments)
    {
        $this->getOwner()->getOwnerProcess()->getDispatcher()->dispatch($event, $arguments);
        array_unshift($arguments, $event);
    }

    /**
     * Evaluate and execute the transition rule.
     *
     * @param ExecutionInstanceInterface $executionInstance
     *
     * @return boolean
     */
    public function execute(ExecutionInstanceInterface $executionInstance)
    {
        $hasAllRequiredTokens = $this->hasAllRequiredTokens($executionInstance);
        if ($hasAllRequiredTokens) {
            $consumeTokens = $this->evaluateConsumeTokens($executionInstance);
            if ($consumeTokens === false) {
                return $this->conditionIsFalse($executionInstance);
            } else {
                return $this->doTransit($consumeTokens, $executionInstance);
            }
        }
        return false;
    }

    /**
     * Evaluate the conditions to obtain the tokens that meet the transition condition.
     *
     * Returns false if the condition evaluates to false.
     *
     * @param ExecutionInstanceInterface $executionInstance
     *
     * @return boolean|\ProcessMaker\Nayra\Bpmn\Collection
     */
    protected function evaluateConsumeTokens(ExecutionInstanceInterface $executionInstance)
    {
        $consumeTokens = [];
        $hasInputTokens = false;
        $pendingTokens = $this->getTokensConsumedPerTransition();
        $this->incoming()->find(function ($flow) use (&$consumeTokens, &$hasInputTokens, $executionInstance, &$pendingTokens) {
            $pendingIncomingTokens = $this->getTokensConsumedPerIncoming();
            $flow->origin()->getTokens($executionInstance)->find(function (TokenInterface $token) use (&$consumeTokens, &$hasInputTokens, $executionInstance, &$pendingIncomingTokens, &$pendingTokens) {
                $hasInputTokens = true;
                $result = $pendingTokens !== 0 && $pendingIncomingTokens !== 0
                    && $this->assertCondition($token, $executionInstance);
                if ($result) {
                    $consumeTokens[] = $token;
                    $pendingIncomingTokens--;
                    $pendingTokens--;
                }
            });
        });
        if ($consumeTokens || (!$hasInputTokens && $this->assertCondition(null, $executionInstance))) {
            return new Collection($consumeTokens);
        } else {
            return false;
        }
    }

    /**
     * Set the number of tokens to be consumed when a transition is activated.
     *
     * @param int $tokensConsumedPerTransition
     *
     * @return $this
     */
    protected function setTokensConsumedPerTransition($tokensConsumedPerTransition)
    {
        $this->tokensConsumedPerTransition = $tokensConsumedPerTransition;
        return $this;
    }

    /**
     * Get the number of tokens to be consumed when a transition is activated.
     *
     * @return int
     */
    protected function getTokensConsumedPerTransition()
    {
        return $this->tokensConsumedPerTransition;
    }

    /**
     * Set the number of tokens that will be consumed per incoming when a transition is activated.
     *
     * @param int $tokensConsumedPerIncoming
     *
     * @return $this
     */
    protected function setTokensConsumedPerIncoming($tokensConsumedPerIncoming)
    {
        $this->tokensConsumedPerIncoming = $tokensConsumedPerIncoming;
        return $this;
    }

    /**
     * Get the number of tokens that will be consumed per incoming when a transition is activated.
     *
     * @return int
     */
    protected function getTokensConsumedPerIncoming()
    {
        return $this->tokensConsumedPerIncoming;
    }

    /**
     * @param boolean $preserveToken
     */
    protected function setPreserveToken($preserveToken)
    {
        $this->preserveToken = $preserveToken;
    }

    /**
     * Get transition owner element
     *
     * @return FlowElementInterface
     */
    public function getOwner()
    {
        return $this->owner;
    }
}
