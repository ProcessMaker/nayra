<?php

namespace ProcessMaker\Nayra\Contracts;

use ProcessMaker\Nayra\Contracts\Bpmn\FlowNodeInterface;
use ProcessMaker\Nayra\Contracts\Repositories\StorageInterface;

/**
 * Repository interface to create BPMN objects.
 *
 */
interface RepositoryInterface
{

    /**
     * Creates an instance of the interface passed
     *
     * @param string $interfaceName Fully qualified name of the interface
     * @param array ...$constructorArguments arguments of class' constructor
     *
     * @return mixed
     */
    public function create($interfaceName, ...$constructorArguments);

    /**
     * Create instance of Activity.
     *
     * @return \ProcessMaker\Nayra\Contracts\Bpmn\ActivityInterface
     */
    public function createActivity();

    /**
     * Create instance of CallActivity.
     *
     * @return \ProcessMaker\Nayra\Contracts\Bpmn\CallActivityInterface
     */
    public function createCallActivity();

    /**
     * Create instance of Collaboration.
     *
     * @return \ProcessMaker\Nayra\Contracts\Bpmn\CollaborationInterface
     */
    public function createCollaboration();

    /**
     * Create instance of ConditionalEventDefinition.
     *
     * @return \ProcessMaker\Nayra\Contracts\Bpmn\ConditionalEventDefinitionInterface
     */
    public function createConditionalEventDefinition();

    /**
     * Create instance of DataInput.
     *
     * @return \ProcessMaker\Nayra\Contracts\Bpmn\DataInputInterface
     */
    public function createDataInput();

    /**
     * Create instance of DataOutput.
     *
     * @return \ProcessMaker\Nayra\Contracts\Bpmn\DataOutputInterface
     */
    public function createDataOutput();

    /**
     * Create instance of DataStore.
     *
     * @return \ProcessMaker\Nayra\Contracts\Bpmn\DataStoreInterface
     */
    public function createDataStore();

    /**
     * Create instance of EndEvent.
     *
     * @return \ProcessMaker\Nayra\Contracts\Bpmn\EndEventInterface
     */
    public function createEndEvent();

    /**
     * Create instance of ErrorEventDefinition.
     *
     * @return \ProcessMaker\Nayra\Contracts\Bpmn\ErrorEventDefinitionInterface
     */
    public function createErrorEventDefinition();

    /**
     * Create instance of Error.
     *
     * @return \ProcessMaker\Nayra\Contracts\Bpmn\ErrorInterface
     */
    public function createError();

    /**
     * Create instance of ExclusiveGateway.
     *
     * @return \ProcessMaker\Nayra\Contracts\Bpmn\ExclusiveGatewayInterface
     */
    public function createExclusiveGateway();

    /**
     * Create instance of Flow.
     *
     * @return \ProcessMaker\Nayra\Contracts\Bpmn\FlowInterface
     */
    public function createFlow();

    /**
     * Create instance of FormalExpression.
     *
     * @return \ProcessMaker\Nayra\Contracts\Bpmn\FormalExpressionInterface
     */
    public function createFormalExpression();

    /**
     * Create instance of InclusiveGateway.
     *
     * @return \ProcessMaker\Nayra\Contracts\Bpmn\InclusiveGatewayInterface
     */
    public function createInclusiveGateway();

    /**
     * Create instance of InputSet.
     *
     * @return \ProcessMaker\Nayra\Contracts\Bpmn\InputSetInterface
     */
    public function createInputSet();

    /**
     * Create instance of IntermediateCatchEvent.
     *
     * @return \ProcessMaker\Nayra\Contracts\Bpmn\IntermediateCatchEventInterface
     */
    public function createIntermediateCatchEvent();

    /**
     * Create instance of IntermediateThrowEvent.
     *
     * @return \ProcessMaker\Nayra\Contracts\Bpmn\IntermediateThrowEventInterface
     */
    public function createIntermediateThrowEvent();

    /**
     * Create instance of ItemDefinition.
     *
     * @return \ProcessMaker\Nayra\Contracts\Bpmn\ItemDefinitionInterface
     */
    public function createItemDefinition();

    /**
     * Create instance of Lane.
     *
     * @return \ProcessMaker\Nayra\Contracts\Bpmn\LaneInterface
     */
    public function createLane();

    /**
     * Create instance of LaneSet.
     *
     * @return \ProcessMaker\Nayra\Contracts\Bpmn\LaneSetInterface
     */
    public function createLaneSet();

    /**
     * Create instance of MessageEventDefinition.
     *
     * @return \ProcessMaker\Nayra\Contracts\Bpmn\MessageEventDefinitionInterface
     */
    public function createMessageEventDefinition();

    /**
     * Create instance of MessageFlow.
     *
     * @return \ProcessMaker\Nayra\Contracts\Bpmn\MessageFlowInterface
     */
    public function createMessageFlow();

    /**
     * Create instance of Message.
     *
     * @return \ProcessMaker\Nayra\Contracts\Bpmn\MessageInterface
     */
    public function createMessage();

    /**
     * Create instance of Operation.
     *
     * @return \ProcessMaker\Nayra\Contracts\Bpmn\OperationInterface
     */
    public function createOperation();

    /**
     * Create instance of OutputSet.
     *
     * @return \ProcessMaker\Nayra\Contracts\Bpmn\OutputSetInterface
     */
    public function createOutputSet();

    /**
     * Create instance of ParallelGateway.
     *
     * @return \ProcessMaker\Nayra\Contracts\Bpmn\ParallelGatewayInterface
     */
    public function createParallelGateway();

    /**
     * Create instance of Participant.
     *
     * @return \ProcessMaker\Nayra\Contracts\Bpmn\ParticipantInterface
     */
    public function createParticipant();

    /**
     * Create instance of Process.
     *
     * @return \ProcessMaker\Nayra\Contracts\Bpmn\ProcessInterface
     */
    public function createProcess();

    /**
     * Create instance of ScriptTask.
     *
     * @return \ProcessMaker\Nayra\Contracts\Bpmn\ScriptTaskInterface
     */
    public function createScriptTask();

    /**
     * Create instance of SignalEventDefinition.
     *
     * @return \ProcessMaker\Nayra\Contracts\Bpmn\SignalEventDefinitionInterface
     */
    public function createSignalEventDefinition();

    /**
     * Create instance of Signal.
     *
     * @return \ProcessMaker\Nayra\Contracts\Bpmn\SignalInterface
     */
    public function createSignal();

    /**
     * Create instance of StartEvent.
     *
     * @return \ProcessMaker\Nayra\Contracts\Bpmn\StartEventInterface
     */
    public function createStartEvent();

    /**
     * Create instance of State.
     *
     * @param FlowNodeInterface $owner
     * @param string $name
     *
     * @return \ProcessMaker\Nayra\Contracts\Bpmn\StateInterface
     */
    public function createState(FlowNodeInterface $owner, $name = '');

    /**
     * Create instance of TerminateEventDefinition.
     *
     * @return \ProcessMaker\Nayra\Contracts\Bpmn\TerminateEventDefinitionInterface
     */
    public function createTerminateEventDefinition();

    /**
     * Create instance of TimerEventDefinition.
     *
     * @return \ProcessMaker\Nayra\Contracts\Bpmn\TimerEventDefinitionInterface
     */
    public function createTimerEventDefinition();

    /**
     * Create instance of Token.
     *
     * @return \ProcessMaker\Nayra\Contracts\Bpmn\TokenInterface
     */
    public function createToken();

    /**
     * Create a execution instance repository.
     *
     * @param StorageInterface $factory
     *
     * @return \ProcessMaker\Test\Models\ExecutionInstanceRepository
     */
    public function createExecutionInstanceRepository(StorageInterface $factory);

    /*
     * Creates a storage interface
     *
     * @return ProcessMaker\Nayra\Contracts\Repositories\StorageInterface
     */
    public function createStorage();
}
