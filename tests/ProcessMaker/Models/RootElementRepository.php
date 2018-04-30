<?php

namespace ProcessMaker\Models;

use ProcessMaker\Nayra\Bpmn\ItemDefinition;
use ProcessMaker\Nayra\Bpmn\MessageEventDefinition;
use ProcessMaker\Nayra\Bpmn\RepositoryTrait;
use ProcessMaker\Nayra\Bpmn\SignalEventDefinition;
use ProcessMaker\Nayra\Contracts\Repositories\RootElementRepositoryInterface;

/**
 * FlowRepository
 *
 * @package ProcessMaker\Models
 */
class RootElementRepository implements RootElementRepositoryInterface
{
    use RepositoryTrait;

    /**
     * Create a new instance.
     *
     * @return \ProcessMaker\Nayra\Contracts\Bpmn\ItemDefinitionInterface
     */
    public function createItemDefinitionInstance(array $properties=[])
    {
        $item = new ItemDefinition();
        $item ->setProperties($properties);
        return $item;
    }

    /**
     * Create a Message.
     *
     * @return \ProcessMaker\Nayra\Contracts\Bpmn\MessageInterface
     */
    public function createMessageInstance()
    {
        return new Message();
    }

    /**
     * Create a MessageEventDefinition.
     *
     * @return \ProcessMaker\Nayra\Contracts\Bpmn\MessageEventDefinitionInterface
     */
    public function createMessageEventDefinitionInstance()
    {
        return new MessageEventDefinition;
    }

    /**
     * Create a formal expression instance.
     *
     * @return \ProcessMaker\Nayra\Contracts\Bpmn\FormalExpressionInterface
     */
    public function createFormalExpressionInstance()
    {
        return new FormalExpression();
    }

    public function createCollaborationInstance()
    {
        return new Collaboration();
    }

    public function createParticipantInstance()
    {
        return new Participant();
    }

    /**
     * Create a signal instance
     *
     * @return Signal
     */
    public function createSignalInstance()
    {
        return new Signal();
    }

    /**
     * Create a signal event definition that is a container of signals
     *
     * @return SignalEventDefinition
     */
    public function createSignalEventDefinitionInstance()
    {
        return new SignalEventDefinition();
    }
}
