<?php

namespace ProcessMaker\Nayra\Bpmn;

use ProcessMaker\Nayra\Bpmn\FlowElementTrait;
use ProcessMaker\Nayra\Bpmn\ObservableTrait;
use ProcessMaker\Nayra\Bpmn\TraversableTrait;
use ProcessMaker\Nayra\Contracts\Bpmn\CollectionInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\ConnectionInterface;
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
     * Initialize the transition.
     *
     * @param FlowNodeInterface $owner
     */
    protected function initTransition(FlowNodeInterface $owner)
    {
        $this->owner = $owner;
        $owner->addTransition($this);
    }

    /**
     * Evaluate true if all the incoming has at least one token.
     *
     * @return boolean
     */
    protected function hasAllRequiredTokens()
    {
        return $this->incoming()->count() > 0 && $this->incoming()->find(function ($flow) {
                return $flow->origin()->getTokens()->count() === 0;
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
     *
     * @return boolean
     */
    protected function doTransit(CollectionInterface $consumeTokens, ExecutionInstanceInterface $executionInstance)
    {
        $this->notifyEvent(TransitionInterface::EVENT_BEFORE_TRANSIT, $this, $consumeTokens);

        $consumeTokens->find(function (TokenInterface $token) use ($executionInstance) {
            $token->getOwner()->consumeToken($token, $executionInstance);
        });

        $this->notifyEvent(TransitionInterface::EVENT_AFTER_CONSUME, $this, $consumeTokens);

        $this->outgoing()->find(function (ConnectionInterface $flow) use ($executionInstance) {
            return $flow->targetState()->addNewToken($executionInstance);
        });

        $this->notifyEvent(TransitionInterface::EVENT_AFTER_TRANSIT, $this, $consumeTokens);

        return true;
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
        $hasAllRequiredTokens = $this->hasAllRequiredTokens();
        if ($hasAllRequiredTokens) {
            $consumeTokens = $this->evaluateConsumeTokens($executionInstance);
            if ($consumeTokens === false) {
                return $this->conditionIsFalse();
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
            $flow->origin()->getTokens()->find(function (TokenInterface $token) use (&$consumeTokens, &$hasInputTokens, $executionInstance, &$pendingIncomingTokens, &$pendingTokens) {
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
}
