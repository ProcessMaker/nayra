<?php

namespace ProcessMaker\Nayra;

use InvalidArgumentException;
use ProcessMaker\Nayra\Bpmn\Lane;
use ProcessMaker\Nayra\Bpmn\LaneSet;
use ProcessMaker\Nayra\Bpmn\Models\Activity;
use ProcessMaker\Nayra\Bpmn\Models\BoundaryEvent;
use ProcessMaker\Nayra\Bpmn\Models\Collaboration;
use ProcessMaker\Nayra\Bpmn\Models\ConditionalEventDefinition;
use ProcessMaker\Nayra\Bpmn\Models\DataInput;
use ProcessMaker\Nayra\Bpmn\Models\DataOutput;
use ProcessMaker\Nayra\Bpmn\Models\DataStore;
use ProcessMaker\Nayra\Bpmn\Models\EndEvent;
use ProcessMaker\Nayra\Bpmn\Models\Error;
use ProcessMaker\Nayra\Bpmn\Models\ErrorEventDefinition;
use ProcessMaker\Nayra\Bpmn\Models\EventBasedGateway;
use ProcessMaker\Nayra\Bpmn\Models\ExclusiveGateway;
use ProcessMaker\Nayra\Bpmn\Models\Flow;
use ProcessMaker\Nayra\Bpmn\Models\InclusiveGateway;
use ProcessMaker\Nayra\Bpmn\Models\InputSet;
use ProcessMaker\Nayra\Bpmn\Models\IntermediateCatchEvent;
use ProcessMaker\Nayra\Bpmn\Models\IntermediateThrowEvent;
use ProcessMaker\Nayra\Bpmn\Models\ItemDefinition;
use ProcessMaker\Nayra\Bpmn\Models\Message;
use ProcessMaker\Nayra\Bpmn\Models\MessageEventDefinition;
use ProcessMaker\Nayra\Bpmn\Models\MessageFlow;
use ProcessMaker\Nayra\Bpmn\Models\MultiInstanceLoopCharacteristics;
use ProcessMaker\Nayra\Bpmn\Models\Operation;
use ProcessMaker\Nayra\Bpmn\Models\OutputSet;
use ProcessMaker\Nayra\Bpmn\Models\ParallelGateway;
use ProcessMaker\Nayra\Bpmn\Models\Participant;
use ProcessMaker\Nayra\Bpmn\Models\Process;
use ProcessMaker\Nayra\Bpmn\Models\ScriptTask;
use ProcessMaker\Nayra\Bpmn\Models\ServiceTask;
use ProcessMaker\Nayra\Bpmn\Models\Signal;
use ProcessMaker\Nayra\Bpmn\Models\SignalEventDefinition;
use ProcessMaker\Nayra\Bpmn\Models\StartEvent;
use ProcessMaker\Nayra\Bpmn\Models\TerminateEventDefinition;
use ProcessMaker\Nayra\Bpmn\Models\TimerEventDefinition;
use ProcessMaker\Nayra\Bpmn\State;
use ProcessMaker\Nayra\Contracts\Bpmn\FlowNodeInterface;
use ProcessMaker\Test\Models\TokenRepository;

/**
 * Class that create instances of classes based on the mappings interface-concrete class passed to it.
 *
 * @package ProcessMaker\Nayra\Contracts
 */
trait RepositoryTrait
{
    private $tokenRepo = null;

    /**
     * Creates an instance of the interface passed
     *
     * @param string $interfaceName Fully qualified name of the interface
     * @param array ...$constructorArguments arguments of class' constructor
     *
     * @return mixed
     */
    public function create($interfaceName, ...$constructorArguments)
    {
        $interfaceParts = explode('\\', $interfaceName);
        $name = array_pop($interfaceParts);
        $method = 'create' . substr($name, 0, -9);
        if (!method_exists($this, $method)) {
            throw new InvalidArgumentException("Can't find $method to instantiate '$interfaceName'");
        }
        return $this->$method(...$constructorArguments);
    }

    /**
     * Create instance of Activity.
     *
     * @return \ProcessMaker\Nayra\Contracts\Bpmn\ActivityInterface
     */
    public function createActivity()
    {
        return new Activity();
    }

    /**
     * Create instance of Collaboration.
     *
     * @return \ProcessMaker\Nayra\Contracts\Bpmn\CollaborationInterface
     */
    public function createCollaboration()
    {
        return new Collaboration();
    }

    /**
     * Create instance of ConditionalEventDefinition.
     *
     * @return \ProcessMaker\Nayra\Contracts\Bpmn\ConditionalEventDefinitionInterface
     */
    public function createConditionalEventDefinition()
    {
        return new ConditionalEventDefinition();
    }

    /**
     * Create instance of DataInput.
     *
     * @return \ProcessMaker\Nayra\Contracts\Bpmn\DataInputInterface
     */
    public function createDataInput()
    {
        return new DataInput();
    }

    /**
     * Create instance of DataOutput.
     *
     * @return \ProcessMaker\Nayra\Contracts\Bpmn\DataOutputInterface
     */
    public function createDataOutput()
    {
        return new DataOutput();
    }

    /**
     * Create instance of DataStore.
     *
     * @return \ProcessMaker\Nayra\Contracts\Bpmn\DataStoreInterface
     */
    public function createDataStore()
    {
        return new DataStore();
    }

    /**
     * Create instance of EndEvent.
     *
     * @return \ProcessMaker\Nayra\Contracts\Bpmn\EndEventInterface
     */
    public function createEndEvent()
    {
        return new EndEvent();
    }

    /**
     * Create instance of ErrorEventDefinition.
     *
     * @return \ProcessMaker\Nayra\Contracts\Bpmn\ErrorEventDefinitionInterface
     */
    public function createErrorEventDefinition()
    {
        return new ErrorEventDefinition();
    }

    /**
     * Create instance of Error.
     *
     * @return \ProcessMaker\Nayra\Contracts\Bpmn\ErrorInterface
     */
    public function createError()
    {
        return new Error();
    }

    /**
     * Create instance of EventBasedGateway.
     *
     * @return \ProcessMaker\Nayra\Contracts\Bpmn\EventBasedGatewayInterface
     */
    public function createEventBasedGateway()
    {
        return new EventBasedGateway();
    }

    /**
     * Create instance of ExclusiveGateway.
     *
     * @return \ProcessMaker\Nayra\Contracts\Bpmn\ExclusiveGatewayInterface
     */
    public function createExclusiveGateway()
    {
        return new ExclusiveGateway();
    }

    /**
     * Create instance of Flow.
     *
     * @return \ProcessMaker\Nayra\Contracts\Bpmn\FlowInterface
     */
    public function createFlow()
    {
        return new Flow();
    }

    /**
     * Create instance of InclusiveGateway.
     *
     * @return \ProcessMaker\Nayra\Contracts\Bpmn\InclusiveGatewayInterface
     */
    public function createInclusiveGateway()
    {
        return new InclusiveGateway();
    }

    /**
     * Create instance of InputSet.
     *
     * @return \ProcessMaker\Nayra\Contracts\Bpmn\InputSetInterface
     */
    public function createInputSet()
    {
        return new InputSet();
    }

    /**
     * Create instance of IntermediateCatchEvent.
     *
     * @return \ProcessMaker\Nayra\Contracts\Bpmn\IntermediateCatchEventInterface
     */
    public function createIntermediateCatchEvent()
    {
        return new IntermediateCatchEvent();
    }

    /**
     * Create instance of IntermediateThrowEvent.
     *
     * @return \ProcessMaker\Nayra\Contracts\Bpmn\IntermediateThrowEventInterface
     */
    public function createIntermediateThrowEvent()
    {
        return new IntermediateThrowEvent();
    }

    /**
     * Create instance of ItemDefinition.
     *
     * @return \ProcessMaker\Nayra\Contracts\Bpmn\ItemDefinitionInterface
     */
    public function createItemDefinition()
    {
        return new ItemDefinition();
    }

    /**
     * Create instance of Lane.
     *
     * @return \ProcessMaker\Nayra\Contracts\Bpmn\LaneInterface
     */
    public function createLane()
    {
        return new Lane();
    }

    /**
     * Create instance of LaneSet.
     *
     * @return \ProcessMaker\Nayra\Contracts\Bpmn\LaneSetInterface
     */
    public function createLaneSet()
    {
        return new LaneSet();
    }

    /**
     * Create instance of MessageEventDefinition.
     *
     * @return \ProcessMaker\Nayra\Contracts\Bpmn\MessageEventDefinitionInterface
     */
    public function createMessageEventDefinition()
    {
        return new MessageEventDefinition();
    }

    /**
     * Create instance of MessageFlow.
     *
     * @return \ProcessMaker\Nayra\Contracts\Bpmn\MessageFlowInterface
     */
    public function createMessageFlow()
    {
        return new MessageFlow();
    }

    /**
     * Create instance of Message.
     *
     * @return \ProcessMaker\Nayra\Contracts\Bpmn\MessageInterface
     */
    public function createMessage()
    {
        return new Message();
    }

    /**
     * Create instance of Operation.
     *
     * @return \ProcessMaker\Nayra\Contracts\Bpmn\OperationInterface
     */
    public function createOperation()
    {
        return new Operation();
    }

    /**
     * Create instance of OutputSet.
     *
     * @return \ProcessMaker\Nayra\Contracts\Bpmn\OutputSetInterface
     */
    public function createOutputSet()
    {
        return new OutputSet();
    }

    /**
     * Create instance of ParallelGateway.
     *
     * @return \ProcessMaker\Nayra\Contracts\Bpmn\ParallelGatewayInterface
     */
    public function createParallelGateway()
    {
        return new ParallelGateway();
    }

    /**
     * Create instance of Participant.
     *
     * @return \ProcessMaker\Nayra\Contracts\Bpmn\ParticipantInterface
     */
    public function createParticipant()
    {
        return new Participant();
    }

    /**
     * Create instance of Process.
     *
     * @return \ProcessMaker\Nayra\Contracts\Bpmn\ProcessInterface
     */
    public function createProcess()
    {
        $process = new Process();
        $process->setRepository($this);
        return $process;
    }

    /**
     * Create instance of ScriptTask.
     *
     * @return \ProcessMaker\Nayra\Contracts\Bpmn\ScriptTaskInterface
     */
    public function createScriptTask()
    {
        return new ScriptTask();
    }

    /**
     * Create instance of ServiceTask.
     *
     * @return \ProcessMaker\Nayra\Contracts\Bpmn\ServiceTaskInterface
     */
    public function createServiceTask()
    {
        return new ServiceTask();
    }

    /**
     * Create instance of SignalEventDefinition.
     *
     * @return \ProcessMaker\Nayra\Contracts\Bpmn\SignalEventDefinitionInterface
     */
    public function createSignalEventDefinition()
    {
        return new SignalEventDefinition();
    }

    /**
     * Create instance of Signal.
     *
     * @return \ProcessMaker\Nayra\Contracts\Bpmn\SignalInterface
     */
    public function createSignal()
    {
        return new Signal();
    }

    /**
     * Create instance of StartEvent.
     *
     * @return \ProcessMaker\Nayra\Contracts\Bpmn\StartEventInterface
     */
    public function createStartEvent()
    {
        return new StartEvent();
    }

    /**
     * Create instance of State.
     *
     * @param FlowNodeInterface $owner
     * @param string $name
     *
     * @return \ProcessMaker\Nayra\Contracts\Bpmn\StateInterface
     */
    public function createState(FlowNodeInterface $owner, $name = '')
    {
        return new State($owner, $name);
    }

    /**
     * Create instance of TerminateEventDefinition.
     *
     * @return \ProcessMaker\Nayra\Contracts\Bpmn\TerminateEventDefinitionInterface
     */
    public function createTerminateEventDefinition()
    {
        return new TerminateEventDefinition();
    }

    /**
     * Create instance of TimerEventDefinition.
     *
     * @return \ProcessMaker\Nayra\Contracts\Bpmn\TimerEventDefinitionInterface
     */
    public function createTimerEventDefinition()
    {
        return new TimerEventDefinition();
    }

    /**
     * Create instance of MultiInstanceLoopCharacteristics.
     *
     * @return \ProcessMaker\Nayra\Contracts\Bpmn\MultiInstanceLoopCharacteristicsInterface
     */
    public function createMultiInstanceLoopCharacteristics()
    {
        return new MultiInstanceLoopCharacteristics();
    }

    /**
     * Create instance of BoundaryEvent
     *
     * @return \ProcessMaker\Nayra\Contracts\Bpmn\BoundaryEventInterface
     */
    public function createBoundaryEvent()
    {
        return new BoundaryEvent();
    }

    /**
     * Creates a TokenRepository
     *
     * @return \ProcessMaker\Nayra\Contracts\Repositories\TokenRepositoryInterface
     */
    public function getTokenRepository()
    {
        if ($this->tokenRepo === null) {
            $this->tokenRepo = new TokenRepository();
        }
        return $this->tokenRepo;
    }
}
