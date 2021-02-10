<?php

namespace ProcessMaker\Nayra\Contracts\Bpmn;

use ProcessMaker\Nayra\Contracts\Engine\ExecutionInstanceInterface;

/**
 * Activities MAY be repeated sequentially, essentially behaving like a loop.
 * The presence of LoopCharacteristics signifies that the Activity has looping
 * behavior. LoopCharacteristics is an abstract class. Concrete subclasses
 * define specific kinds of looping behavior.
 *
 * @package ProcessMaker\Nayra\Contracts\Bpmn
 */
interface LoopCharacteristicsInterface extends EntityInterface
{
    const BPMN_LOOP_INSTANCE_PROPERTY = 'loopCharacteristics';

    /**
     * Iterate the loop action
     *
     * @param StateInterface $nextState
     * @param ExecutionInstanceInterface $instance
     * @param CollectionInterface $consumeTokens
     * @param array $properties
     * @param TransitionInterface|null $source
     *
     * @return void
     */
    public function iterateNextState(StateInterface $nextState, ExecutionInstanceInterface $instance, CollectionInterface $consumeTokens, array $properties = [], TransitionInterface $source = null);

    /**
     * When a token is completed
     *
     * @param TokenInterface $token
     *
     * @return void
     */
    public function onTokenCompleted(TokenInterface $token);

    /**
     * When a token is terminated
     *
     * @param TokenInterface $token
     *
     * @return void
     */
    public function onTokenTerminated(TokenInterface $token);

    /**
     * Check if the loop was completed
     *
     * @param ExecutionInstanceInterface $instance
     * @param TokenInterface $token
     *
     * @return boolean
     */
    public function isLoopCompleted(ExecutionInstanceInterface $instance, TokenInterface $token);

    /**
     * Check if the loop should continue
     *
     * @param ExecutionInstanceInterface $instance
     * @param TokenInterface $token
     *
     * @return bool
     */
    public function continueLoop(ExecutionInstanceInterface $instance, TokenInterface $token);
}
