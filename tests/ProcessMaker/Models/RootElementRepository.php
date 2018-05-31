<?php

namespace ProcessMaker\Models;

use ProcessMaker\Nayra\Bpmn\Models\ConditionalEventDefinition;
use ProcessMaker\Nayra\Bpmn\Models\Error;
use ProcessMaker\Nayra\Bpmn\Models\ErrorEventDefinition;
use ProcessMaker\Nayra\Bpmn\Models\FormalExpression;
use ProcessMaker\Nayra\Bpmn\Models\ItemDefinition;
use ProcessMaker\Nayra\Bpmn\Models\MessageEventDefinition;
use ProcessMaker\Nayra\Bpmn\Models\Collaboration;
use ProcessMaker\Nayra\Bpmn\Models\Message;
use ProcessMaker\Nayra\Bpmn\Models\Participant;
use ProcessMaker\Nayra\Bpmn\Models\Signal;
use ProcessMaker\Nayra\Bpmn\RepositoryTrait;
use ProcessMaker\Nayra\Bpmn\Models\SignalEventDefinition;
use ProcessMaker\Nayra\Bpmn\Models\TerminateEventDefinition;
use ProcessMaker\Nayra\Bpmn\Models\TimerEventDefinition;
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
     * @param array $properties
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

    /**
     * Create a collaboration instance.
     *
     * @return \ProcessMaker\Nayra\Bpmn\Models\Collaboration
     */
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

    /**
     * Create a signal event definition that is a container of signals
     *
     * @return ConditionalEventDefinition
     */
    public function createConditionalEventDefinitionInstance()
    {
        return new ConditionalEventDefinition();
    }

    /**
     * Create a TerminateEventDefinition.
     *
     * @return TerminateEventDefinition
     */
    public function createTerminateEventDefinitionInstance()
    {
        return new TerminateEventDefinition();
    }

    /**
     * Create a ErrorEventDefinition.
     *
     * @return ErrorEventDefinition
     */
    public function createErrorEventDefinitionInstance()
    {
        return new ErrorEventDefinition();
    }

    /**
     * Create a error instance
     *
     * @return Error
     */
    public function createErrorInstance()
    {
        return new Error();
    }

    /**
     * Create a TimerEventDefinition.
     *
     * @return TimerEventDefinition
     */
    public function createTimerEventDefinitionInstance()
    {
        return new TimerEventDefinition();
    }
}
