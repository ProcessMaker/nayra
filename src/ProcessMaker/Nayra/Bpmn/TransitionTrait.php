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
                $flow->origin()->getTokens()->find(function (TokenInterface $token) use (&$consumeTokens, &$hasInputTokens, $executionInstance) {
                    $hasInputTokens = true;
                    $result = $this->assertCondition($token, $executionInstance);
                    if ($result) {
                        $consumeTokens[] = $token;
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
