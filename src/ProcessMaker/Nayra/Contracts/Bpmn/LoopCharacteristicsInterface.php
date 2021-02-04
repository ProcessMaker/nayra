<?php

namespace ProcessMaker\Nayra\Contracts\Bpmn;

use ProcessMaker\Nayra\Contracts\Engine\ExecutionInstanceInterface;

interface LoopCharacteristicsInterface extends EntityInterface
{
    const BPMN_LOOP_INSTANCE_PROPERTY = 'loopCharacteristics';

    /**
     * Iterate the loop action
     *
     * @param StateInterface $nextState
     * @param ExecutionInstanceInterface $instance
     * @param array $properties
     * @param TransitionInterface $source
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
}
