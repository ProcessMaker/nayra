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
 *
 */
trait TransitionTrait
{

    use FlowElementTrait,
        TraversableTrait,
        ObservableTrait;

    private $owner;

    /**
     * When the transition is activated one or more tokens could be consumed.
     *  Default = 1
     *  0 or -1 = Means no limit.
     *
     * @var int $tokensConsumedPerTransition
     */
    protected $tokensConsumedPerTransition = 1;

    protected function initTransition(FlowNodeInterface $owner)
    {
        $this->owner = $owner;
        $owner->addTransition($this);
    }

    public function getTokens()
    {
        return new Collection();
    }

    protected function hasAllRequiredTokens()
    {
        return $this->incoming()->count() > 0 && $this->incoming()->find(function ($flow) {
                return $flow->origin()->getTokens()->count() === 0;
            })->count() === 0;
    }

    abstract public function assertCondition(TokenInterface $token);

    protected function conditionIsFalse()
    {
        return false;
    }

    protected function doTransit(CollectionInterface $consumeTokens)
    {
        $this->notifyEvent(TransitionInterface::EVENT_BEFORE_TRANSIT, $this);

        $consumeTokens->find(function (TokenInterface $token) {
            $token->getOwner()->consumeToken($token);
        });

        $this->notifyEvent(TransitionInterface::EVENT_AFTER_TRANSIT, $this);

        $this->outgoing()->find(function (ConnectionInterface $flow) {
            return $flow->targetState()->addNewToken();
        });

        return true;
    }

    public function execute(ExecutionInstanceInterface $executionInstance)
    {
        $consumeTokens = [];
        $hasAllRequiredTokens = $this->hasAllRequiredTokens();
        if ($hasAllRequiredTokens) {
            $hasInputTokens = false;
            $this->incoming()->find(function ($flow) use (&$consumeTokens, &$hasInputTokens, $executionInstance) {
                $pendingTokens = $this->tokensConsumedPerTransition;
                $flow->origin()->getTokens()->find(function (TokenInterface $token) use (&$consumeTokens, &$hasInputTokens, $executionInstance, &$pendingTokens) {
                    $hasInputTokens = true;
                    $result = $pendingTokens !== 0
                        && $this->assertCondition($token, $executionInstance);
                    if ($result) {
                        $consumeTokens[] = $token;
                        $pendingTokens--;
                    }
                });
            });
            if ($consumeTokens || (!$hasInputTokens && $this->assertCondition(null, $executionInstance))) {
                return $this->doTransit(new Collection($consumeTokens));
            } else {
                return $this->conditionIsFalse();
            }
        }
        return false;
    }
}
