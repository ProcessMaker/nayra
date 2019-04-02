<?php

namespace ProcessMaker\Nayra\Bpmn;

use ProcessMaker\Nayra\Contracts\Bpmn\TokenInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\TransitionInterface;
use ProcessMaker\Nayra\Contracts\Engine\ExecutionInstanceInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\EventBasedGatewayInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\IntermediateCatchEventInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\CollectionInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\CatchEventInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\FlowElementInterface;

/**
 * Verify the condition to transit following the exclusive transition rules.
 * If not accomplished the tokens are consumed.
 *
 * @package ProcessMaker\Nayra\Bpmn
 */
class EventBasedTransition implements TransitionInterface
{
    use TransitionTrait;

    /**
     * @var callable $condition
     */
    private $condition;

    protected function initEventBasedTransition($owner, CatchEventInterface $event)
    {
        $event->getActivationTransition()->attachEvent(TransitionInterface::EVENT_AFTER_TRANSIT, function (IntermediateCatchEventTransition $transition, CollectionInterface $passedTokens) {
            $passedToken = $passedTokens->item(0);
            $consumedTokens = $this->removeTokenFromConnectedEvents($transition->getOwner(), $passedToken);
            $this->getOwner()->getRepository()
                    ->getTokenRepository()
                    ->persistEventBasedGatewayActivated($this->getOwner(), $passedToken, $consumedTokens);
            $this->notifyEvent(EventBasedGatewayInterface::EVENT_CATCH_EVENT_TRIGGERED, $this, $passedToken, $consumedTokens);
        });
    }

    /**
     * Removes a token from the next events to the EventBasedGateway
     *
     * @return $this
     */
    private function removeTokenFromConnectedEvents(FlowElementInterface $activatedEvent, $token)
    {
        $consumedTokens = [];
        foreach ($this->owner->getNextEventElements() as $event) {
            if ($event->getId() === $activatedEvent->getId()) {
                // Skip already activated event
                continue;
            }
            $state = $event->getActiveState();
            $tokens = $state->getTokens($token->getInstance())->toArray();
            $token = array_shift($tokens);
            $token->setStatus(IntermediateCatchEventInterface::TOKEN_STATE_CLOSED);
            $state->consumeToken($token);
            $consumedTokens[] = $token;
        }
        return new Collection($consumedTokens);
    }

    /**
     * Condition required to transit the element.
     *
     * @param TokenInterface $token
     * @param ExecutionInstanceInterface $executionInstance
     *
     * @return mixed
     */
    public function assertCondition(TokenInterface $token = null, ExecutionInstanceInterface $executionInstance)
    {
        return true;
    }
}
