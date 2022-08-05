<?php

namespace ProcessMaker\Nayra\Storage;

use DOMDocument;
use DOMElement;
use DOMXPath;
use ProcessMaker\Nayra\Contracts\Bpmn\ActivityInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\BoundaryEventInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\CallActivityInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\CatchEventInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\CollaborationInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\ConditionalEventDefinitionInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\DataInputInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\DataOutputInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\DataStoreInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\EndEventInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\EntityInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\ErrorEventDefinitionInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\ErrorInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\EventBasedGatewayInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\EventDefinitionInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\EventInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\ExclusiveGatewayInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\FlowInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\FlowNodeInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\FormalExpressionInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\GatewayInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\InclusiveGatewayInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\InputOutputSpecificationInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\InputSetInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\IntermediateCatchEventInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\IntermediateThrowEventInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\ItemDefinitionInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\LaneInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\LaneSetInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\LoopCharacteristicsInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\MessageEventDefinitionInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\MessageFlowInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\MessageInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\MultiInstanceLoopCharacteristicsInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\OperationInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\OutputSetInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\ParallelGatewayInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\ParticipantInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\ProcessInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\ScriptTaskInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\ServiceInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\ServiceTaskInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\SignalEventDefinitionInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\SignalInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\StandardLoopCharacteristicsInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\StartEventInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\TerminateEventDefinitionInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\TimerEventDefinitionInterface;
use ProcessMaker\Nayra\Contracts\Engine\EngineInterface;
use ProcessMaker\Nayra\Contracts\RepositoryInterface;
use ProcessMaker\Nayra\Contracts\Storage\BpmnDocumentInterface;
use ProcessMaker\Nayra\Exceptions\ElementNotFoundException;

/**
 * BPMN file
 */
class BpmnDocument extends DOMDocument implements BpmnDocumentInterface
{
    const BPMN_MODEL = 'http://www.omg.org/spec/BPMN/20100524/MODEL';

    /**
     * @var \ProcessMaker\Nayra\Contracts\Bpmn\EntityInterface
     */
    private $bpmnElements = [];

    /**
     * @var \ProcessMaker\Nayra\Contracts\Engine\EngineInterface
     */
    private $engine;

    /**
     * @var \ProcessMaker\Nayra\Contracts\FactoryInterface
     */
    private $factory;

    /**
     * @var bool
     */
    private $skipElementsNotImplemented = false;

    /**
     * BPMNValidator errors.
     *
     * @var array
     */
    private $validationErrors = [];

    private $mapping = [
        'http://www.omg.org/spec/BPMN/20100524/MODEL' => [
            'process' => [
                ProcessInterface::class,
                [
                    'activities' => ['n', ActivityInterface::class],
                    'gateways' => ['n', GatewayInterface::class],
                    'events' => ['n', EventInterface::class],
                    ProcessInterface::BPMN_PROPERTY_LANE_SET => ['n', [self::BPMN_MODEL, ProcessInterface::BPMN_PROPERTY_LANE_SET]],
                ],
            ],
            'startEvent' => [
                StartEventInterface::class,
                [
                    FlowNodeInterface::BPMN_PROPERTY_INCOMING => ['n', [self::BPMN_MODEL, FlowNodeInterface::BPMN_PROPERTY_INCOMING]],
                    FlowNodeInterface::BPMN_PROPERTY_OUTGOING => ['n', [self::BPMN_MODEL, FlowNodeInterface::BPMN_PROPERTY_OUTGOING]],
                    StartEventInterface::BPMN_PROPERTY_EVENT_DEFINITIONS => ['n', EventDefinitionInterface::class],
                ],
            ],
            'endEvent' => [
                EndEventInterface::class,
                [
                    FlowNodeInterface::BPMN_PROPERTY_INCOMING => ['n', [self::BPMN_MODEL, FlowNodeInterface::BPMN_PROPERTY_INCOMING]],
                    FlowNodeInterface::BPMN_PROPERTY_OUTGOING => ['n', [self::BPMN_MODEL, FlowNodeInterface::BPMN_PROPERTY_OUTGOING]],
                    EndEventInterface::BPMN_PROPERTY_EVENT_DEFINITIONS => ['n', EventDefinitionInterface::class],
                ],
            ],
            'task' => [
                ActivityInterface::class,
                [
                    FlowNodeInterface::BPMN_PROPERTY_INCOMING => ['n', [self::BPMN_MODEL, FlowNodeInterface::BPMN_PROPERTY_INCOMING]],
                    FlowNodeInterface::BPMN_PROPERTY_OUTGOING => ['n', [self::BPMN_MODEL, FlowNodeInterface::BPMN_PROPERTY_OUTGOING]],
                    ActivityInterface::BPMN_PROPERTY_LOOP_CHARACTERISTICS => ['1', LoopCharacteristicsInterface::class],
                    ActivityInterface::BPMN_PROPERTY_IO_SPECIFICATION => ['1', [self::BPMN_MODEL, ActivityInterface::BPMN_PROPERTY_IO_SPECIFICATION]],
                ],
            ],
            'userTask' => [
                ActivityInterface::class,
                [
                    FlowNodeInterface::BPMN_PROPERTY_INCOMING => ['n', [self::BPMN_MODEL, FlowNodeInterface::BPMN_PROPERTY_INCOMING]],
                    FlowNodeInterface::BPMN_PROPERTY_OUTGOING => ['n', [self::BPMN_MODEL, FlowNodeInterface::BPMN_PROPERTY_OUTGOING]],
                    ActivityInterface::BPMN_PROPERTY_LOOP_CHARACTERISTICS => ['1', LoopCharacteristicsInterface::class],
                    ActivityInterface::BPMN_PROPERTY_IO_SPECIFICATION => ['1', [self::BPMN_MODEL, ActivityInterface::BPMN_PROPERTY_IO_SPECIFICATION]],
                ],
            ],
            'scriptTask' => [
                ScriptTaskInterface::class,
                [
                    FlowNodeInterface::BPMN_PROPERTY_INCOMING => ['n', [self::BPMN_MODEL, FlowNodeInterface::BPMN_PROPERTY_INCOMING]],
                    FlowNodeInterface::BPMN_PROPERTY_OUTGOING => ['n', [self::BPMN_MODEL, FlowNodeInterface::BPMN_PROPERTY_OUTGOING]],
                    ScriptTaskInterface::BPMN_PROPERTY_SCRIPT => ['1', [self::BPMN_MODEL, ScriptTaskInterface::BPMN_PROPERTY_SCRIPT]],
                    ActivityInterface::BPMN_PROPERTY_LOOP_CHARACTERISTICS => ['1', LoopCharacteristicsInterface::class],
                    ActivityInterface::BPMN_PROPERTY_IO_SPECIFICATION => ['1', [self::BPMN_MODEL, ActivityInterface::BPMN_PROPERTY_IO_SPECIFICATION]],
                ],
            ],
            'serviceTask' => [
                ServiceTaskInterface::class,
                [
                    FlowNodeInterface::BPMN_PROPERTY_INCOMING => ['n', [self::BPMN_MODEL, FlowNodeInterface::BPMN_PROPERTY_INCOMING]],
                    FlowNodeInterface::BPMN_PROPERTY_OUTGOING => ['n', [self::BPMN_MODEL, FlowNodeInterface::BPMN_PROPERTY_OUTGOING]],
                    ActivityInterface::BPMN_PROPERTY_LOOP_CHARACTERISTICS => ['1', LoopCharacteristicsInterface::class],
                    ActivityInterface::BPMN_PROPERTY_IO_SPECIFICATION => ['1', [self::BPMN_MODEL, ActivityInterface::BPMN_PROPERTY_IO_SPECIFICATION]],
                ],
            ],
            FlowNodeInterface::BPMN_PROPERTY_OUTGOING => [self::IS_REFERENCE, []],
            FlowNodeInterface::BPMN_PROPERTY_INCOMING => [self::IS_REFERENCE, []],
            'sequenceFlow' => [
                FlowInterface::class,
                [
                    FlowInterface::BPMN_PROPERTY_SOURCE => ['1', [self::BPMN_MODEL, FlowInterface::BPMN_PROPERTY_SOURCE_REF]],
                    FlowInterface::BPMN_PROPERTY_TARGET => ['1', [self::BPMN_MODEL, FlowInterface::BPMN_PROPERTY_TARGET_REF]],
                    FlowInterface::BPMN_PROPERTY_CONDITION_EXPRESSION => ['1', [self::BPMN_MODEL, 'conditionExpression']],
                ],
            ],
            'callActivity' => [
                CallActivityInterface::class,
                [
                    FlowNodeInterface::BPMN_PROPERTY_INCOMING => ['n', [self::BPMN_MODEL, FlowNodeInterface::BPMN_PROPERTY_INCOMING]],
                    FlowNodeInterface::BPMN_PROPERTY_OUTGOING => ['n', [self::BPMN_MODEL, FlowNodeInterface::BPMN_PROPERTY_OUTGOING]],
                    CallActivityInterface::BPMN_PROPERTY_CALLED_ELEMENT => ['1', [self::BPMN_MODEL, CallActivityInterface::BPMN_PROPERTY_CALLED_ELEMENT]],
                    ActivityInterface::BPMN_PROPERTY_LOOP_CHARACTERISTICS => ['1', LoopCharacteristicsInterface::class],
                    ActivityInterface::BPMN_PROPERTY_IO_SPECIFICATION => ['1', [self::BPMN_MODEL, ActivityInterface::BPMN_PROPERTY_IO_SPECIFICATION]],
                ],
            ],
            'parallelGateway' => [
                ParallelGatewayInterface::class,
                [
                    FlowNodeInterface::BPMN_PROPERTY_INCOMING => ['n', [self::BPMN_MODEL, FlowNodeInterface::BPMN_PROPERTY_INCOMING]],
                    FlowNodeInterface::BPMN_PROPERTY_OUTGOING => ['n', [self::BPMN_MODEL, FlowNodeInterface::BPMN_PROPERTY_OUTGOING]],
                ],
            ],
            'inclusiveGateway' => [
                InclusiveGatewayInterface::class,
                [
                    FlowNodeInterface::BPMN_PROPERTY_INCOMING => ['n', [self::BPMN_MODEL, FlowNodeInterface::BPMN_PROPERTY_INCOMING]],
                    FlowNodeInterface::BPMN_PROPERTY_OUTGOING => ['n', [self::BPMN_MODEL, FlowNodeInterface::BPMN_PROPERTY_OUTGOING]],
                    GatewayInterface::BPMN_PROPERTY_DEFAULT => ['1', [self::BPMN_MODEL, GatewayInterface::BPMN_PROPERTY_DEFAULT]],
                ],
            ],
            'exclusiveGateway' => [
                ExclusiveGatewayInterface::class,
                [
                    FlowNodeInterface::BPMN_PROPERTY_INCOMING => ['n', [self::BPMN_MODEL, FlowNodeInterface::BPMN_PROPERTY_INCOMING]],
                    FlowNodeInterface::BPMN_PROPERTY_OUTGOING => ['n', [self::BPMN_MODEL, FlowNodeInterface::BPMN_PROPERTY_OUTGOING]],
                    GatewayInterface::BPMN_PROPERTY_DEFAULT => ['1', [self::BPMN_MODEL, GatewayInterface::BPMN_PROPERTY_DEFAULT]],
                ],
            ],
            'eventBasedGateway' => [
                EventBasedGatewayInterface::class,
                [
                    FlowNodeInterface::BPMN_PROPERTY_INCOMING => ['n', [self::BPMN_MODEL, FlowNodeInterface::BPMN_PROPERTY_INCOMING]],
                    FlowNodeInterface::BPMN_PROPERTY_OUTGOING => ['n', [self::BPMN_MODEL, FlowNodeInterface::BPMN_PROPERTY_OUTGOING]],
                ],
            ],
            'conditionExpression' => [
                FormalExpressionInterface::class,
                [
                    FormalExpressionInterface::BPMN_PROPERTY_BODY => ['1', self::DOM_ELEMENT_BODY],
                ],
            ],
            'script' => [self::TEXT_PROPERTY, []],
            'collaboration' => [
                CollaborationInterface::class,
                [
                    CollaborationInterface::BPMN_PROPERTY_PARTICIPANT => ['n', [self::BPMN_MODEL, CollaborationInterface::BPMN_PROPERTY_PARTICIPANT]],
                    CollaborationInterface::BPMN_PROPERTY_MESSAGE_FLOWS => ['n', [self::BPMN_MODEL, CollaborationInterface::BPMN_PROPERTY_MESSAGE_FLOW]],
                ],
            ],
            'participant' => [
                ParticipantInterface::class,
                [
                    ParticipantInterface::BPMN_PROPERTY_PROCESS => ['1', [self::BPMN_MODEL, ParticipantInterface::BPMN_PROPERTY_PROCESS_REF]],
                    ParticipantInterface::BPMN_PROPERTY_PARTICIPANT_MULTIPICITY => ['1', [self::BPMN_MODEL, ParticipantInterface::BPMN_PROPERTY_PARTICIPANT_MULTIPICITY]],
                ],
            ],
            'participantMultiplicity' => [self::IS_ARRAY, []],
            'conditionalEventDefinition' => [
                ConditionalEventDefinitionInterface::class,
                [
                    ConditionalEventDefinitionInterface::BPMN_PROPERTY_CONDITION => ['1', [self::BPMN_MODEL, ConditionalEventDefinitionInterface::BPMN_PROPERTY_CONDITION]],
                ],
            ],
            'condition' => [
                FormalExpressionInterface::class,
                [
                    FormalExpressionInterface::BPMN_PROPERTY_BODY => ['1', self::DOM_ELEMENT_BODY],
                ],
            ],
            'extensionElements' => self::SKIP_ELEMENT,
            'documentation' => self::SKIP_ELEMENT,
            'inputSet' => [
                InputSetInterface::class,
                [
                    InputSetInterface::BPMN_PROPERTY_DATA_INPUTS => ['n', [self::BPMN_MODEL, InputSetInterface::BPMN_PROPERTY_DATA_INPUT_REFS]],
                ],
            ],
            InputSetInterface::BPMN_PROPERTY_DATA_INPUT_REFS => [self::IS_REFERENCE, []],
            'outputSet' => [
                OutputSetInterface::class,
                [
                    OutputSetInterface::BPMN_PROPERTY_DATA_OUTPUTS => ['n', [self::BPMN_MODEL, OutputSetInterface::BPMN_PROPERTY_DATA_OUTPUT_REFS]],
                ],
            ],
            OutputSetInterface::BPMN_PROPERTY_DATA_OUTPUT_REFS => [self::IS_REFERENCE, []],
            'terminateEventDefinition' => [
                TerminateEventDefinitionInterface::class,
                [
                ],
            ],
            'errorEventDefinition' => [
                ErrorEventDefinitionInterface::class,
                [
                    ErrorEventDefinitionInterface::BPMN_PROPERTY_ERROR => ['1', [self::BPMN_MODEL, ErrorEventDefinitionInterface::BPMN_PROPERTY_ERROR_REF]],
                ],
            ],
            'error' => [
                ErrorInterface::class,
                [
                ],
            ],
            'messageFlow' => [
                MessageFlowInterface::class,
                [
                    MessageFlowInterface::BPMN_PROPERTY_SOURCE => ['1', [self::BPMN_MODEL, MessageFlowInterface::BPMN_PROPERTY_SOURCE_REF]],
                    MessageFlowInterface::BPMN_PROPERTY_TARGET => ['1', [self::BPMN_MODEL, MessageFlowInterface::BPMN_PROPERTY_TARGET_REF]],
                    MessageFlowInterface::BPMN_PROPERTY_COLLABORATION => self::PARENT_NODE,
                ],
            ],
            'timerEventDefinition' => [
                TimerEventDefinitionInterface::class,
                [
                    TimerEventDefinitionInterface::BPMN_PROPERTY_TIME_DATE => ['1', [self::BPMN_MODEL, TimerEventDefinitionInterface::BPMN_PROPERTY_TIME_DATE]],
                    TimerEventDefinitionInterface::BPMN_PROPERTY_TIME_CYCLE => ['1', [self::BPMN_MODEL, TimerEventDefinitionInterface::BPMN_PROPERTY_TIME_CYCLE]],
                    TimerEventDefinitionInterface::BPMN_PROPERTY_TIME_DURATION => ['1', [self::BPMN_MODEL, TimerEventDefinitionInterface::BPMN_PROPERTY_TIME_DURATION]],
                ],
            ],
            TimerEventDefinitionInterface::BPMN_PROPERTY_TIME_DATE => [
                FormalExpressionInterface::class,
                [
                    FormalExpressionInterface::BPMN_PROPERTY_BODY => ['1', self::DOM_ELEMENT_BODY],
                ],
            ],
            TimerEventDefinitionInterface::BPMN_PROPERTY_TIME_CYCLE => [
                FormalExpressionInterface::class,
                [
                    FormalExpressionInterface::BPMN_PROPERTY_BODY => ['1', self::DOM_ELEMENT_BODY],
                ],
            ],
            TimerEventDefinitionInterface::BPMN_PROPERTY_TIME_DURATION => [
                FormalExpressionInterface::class,
                [
                    FormalExpressionInterface::BPMN_PROPERTY_BODY => ['1', self::DOM_ELEMENT_BODY],
                ],
            ],
            'laneSet' => [
                LaneSetInterface::class,
                [
                    LaneSetInterface::BPMN_PROPERTY_LANE => ['n', [self::BPMN_MODEL, LaneSetInterface::BPMN_PROPERTY_LANE]],
                ],
            ],
            'lane' => [
                LaneInterface::class,
                [
                    LaneInterface::BPMN_PROPERTY_FLOW_NODE => ['n', [self::BPMN_MODEL, LaneInterface::BPMN_PROPERTY_FLOW_NODE_REF]],
                    LaneInterface::BPMN_PROPERTY_CHILD_LANE_SET => ['n', [self::BPMN_MODEL, LaneInterface::BPMN_PROPERTY_CHILD_LANE_SET]],
                ],
            ],
            LaneInterface::BPMN_PROPERTY_FLOW_NODE_REF => [self::IS_REFERENCE, []],
            LaneInterface::BPMN_PROPERTY_CHILD_LANE_SET => [
                LaneSetInterface::class,
                [
                    LaneSetInterface::BPMN_PROPERTY_LANE => ['n', [self::BPMN_MODEL, LaneSetInterface::BPMN_PROPERTY_LANE]],
                ],
            ],
            'interface' => [
                ServiceInterface::class,
                [
                    ServiceInterface::BPMN_PROPERTY_OPERATIONS => ['n', [self::BPMN_MODEL, OperationInterface::BPMN_TAG]],
                ],
            ],
            OperationInterface::BPMN_TAG => [
                OperationInterface::class,
                [
                    OperationInterface::BPMN_PROPERTY_IN_MESSAGE => ['n', [self::BPMN_MODEL, OperationInterface::BPMN_PROPERTY_IN_MESSAGE_REF]],
                    OperationInterface::BPMN_PROPERTY_OUT_MESSAGE => ['n', [self::BPMN_MODEL, OperationInterface::BPMN_PROPERTY_OUT_MESSAGE_REF]],
                    OperationInterface::BPMN_PROPERTY_ERRORS => ['n', [self::BPMN_MODEL, OperationInterface::BPMN_PROPERTY_ERROR_REF]],
                ],
            ],
            OperationInterface::BPMN_PROPERTY_IN_MESSAGE_REF => [self::IS_REFERENCE, []],
            OperationInterface::BPMN_PROPERTY_OUT_MESSAGE_REF => [self::IS_REFERENCE, []],
            OperationInterface::BPMN_PROPERTY_ERROR_REF => [self::IS_REFERENCE, []],
            'messageEventDefinition' => [
                MessageEventDefinitionInterface::class,
                [
                    MessageEventDefinitionInterface::BPMN_PROPERTY_OPERATION => ['1', [self::BPMN_MODEL, MessageEventDefinitionInterface::BPMN_PROPERTY_OPERATION_REF]],
                    MessageEventDefinitionInterface::BPMN_PROPERTY_MESSAGE => ['1', [self::BPMN_MODEL, MessageEventDefinitionInterface::BPMN_PROPERTY_MESSAGE_REF]],
                ],
            ],
            MessageEventDefinitionInterface::BPMN_PROPERTY_OPERATION_REF => [self::IS_REFERENCE, []],
            'message' => [
                MessageInterface::class,
                [
                    MessageInterface::BPMN_PROPERTY_ITEM => ['1', [self::BPMN_MODEL, MessageInterface::BPMN_PROPERTY_ITEM_REF]],
                ],
            ],
            'intermediateCatchEvent' => [
                IntermediateCatchEventInterface::class,
                [
                    FlowNodeInterface::BPMN_PROPERTY_INCOMING => ['n', [self::BPMN_MODEL, FlowNodeInterface::BPMN_PROPERTY_INCOMING]],
                    FlowNodeInterface::BPMN_PROPERTY_OUTGOING => ['n', [self::BPMN_MODEL, FlowNodeInterface::BPMN_PROPERTY_OUTGOING]],
                    IntermediateCatchEventInterface::BPMN_PROPERTY_EVENT_DEFINITIONS => ['n', EventDefinitionInterface::class],
                ],
            ],
            'intermediateThrowEvent' => [
                IntermediateThrowEventInterface::class,
                [
                    FlowNodeInterface::BPMN_PROPERTY_INCOMING => ['n', [self::BPMN_MODEL, FlowNodeInterface::BPMN_PROPERTY_INCOMING]],
                    FlowNodeInterface::BPMN_PROPERTY_OUTGOING => ['n', [self::BPMN_MODEL, FlowNodeInterface::BPMN_PROPERTY_OUTGOING]],
                    IntermediateThrowEventInterface::BPMN_PROPERTY_EVENT_DEFINITIONS => ['n', EventDefinitionInterface::class],
                ],
            ],
            'signalEventDefinition' => [
                SignalEventDefinitionInterface::class,
                [
                    SignalEventDefinitionInterface::BPMN_PROPERTY_SIGNAL => ['1', [self::BPMN_MODEL, SignalEventDefinitionInterface::BPMN_PROPERTY_SIGNAL_REF]],
                ],
            ],
            'itemDefinition' => [
                ItemDefinitionInterface::class,
                [
                ],
            ],
            'signal' => [
                SignalInterface::class,
                [
                ],
            ],
            'dataInput' => [
                DataInputInterface::class,
                [
                    DataInputInterface::BPMN_PROPERTY_ITEM_SUBJECT => ['1', [self::BPMN_MODEL, DataInputInterface::BPMN_PROPERTY_ITEM_SUBJECT_REF]],
                    DataInputInterface::BPMN_PROPERTY_IS_COLLECTION => self::IS_BOOLEAN,
                ],
            ],
            'dataOutput' => [
                DataOutputInterface::class,
                [
                    DataOutputInterface::BPMN_PROPERTY_ITEM_SUBJECT => ['1', [self::BPMN_MODEL, DataOutputInterface::BPMN_PROPERTY_ITEM_SUBJECT_REF]],
                    DataOutputInterface::BPMN_PROPERTY_IS_COLLECTION => self::IS_BOOLEAN,
                ],
            ],
            'dataStore' => [
                DataStoreInterface::class,
                [],
            ],
            'boundaryEvent' => [
                BoundaryEventInterface::class,
                [
                    BoundaryEventInterface::BPMN_PROPERTY_CANCEL_ACTIVITY => self::IS_BOOLEAN,
                    BoundaryEventInterface::BPMN_PROPERTY_ATTACHED_TO => ['1', [self::BPMN_MODEL, BoundaryEventInterface::BPMN_PROPERTY_ATTACHED_TO_REF]],
                    CatchEventInterface::BPMN_PROPERTY_EVENT_DEFINITIONS => ['n', EventDefinitionInterface::class],
                    FlowNodeInterface::BPMN_PROPERTY_OUTGOING => ['n', [self::BPMN_MODEL, FlowNodeInterface::BPMN_PROPERTY_OUTGOING]],
                ],
            ],
            'multiInstanceLoopCharacteristics' => [
                MultiInstanceLoopCharacteristicsInterface::class,
                [
                    MultiInstanceLoopCharacteristicsInterface::BPMN_PROPERTY_IS_SEQUENTIAL => self::IS_BOOLEAN,
                    MultiInstanceLoopCharacteristicsInterface::BPMN_PROPERTY_LOOP_CARDINALITY => ['1', [self::BPMN_MODEL, MultiInstanceLoopCharacteristicsInterface::BPMN_PROPERTY_LOOP_CARDINALITY]],
                    MultiInstanceLoopCharacteristicsInterface::BPMN_PROPERTY_COMPLETION_CONDITION => ['1', [self::BPMN_MODEL, MultiInstanceLoopCharacteristicsInterface::BPMN_PROPERTY_COMPLETION_CONDITION]],
                    MultiInstanceLoopCharacteristicsInterface::BPMN_PROPERTY_LOOP_DATA_INPUT => ['1', [self::BPMN_MODEL, MultiInstanceLoopCharacteristicsInterface::BPMN_PROPERTY_LOOP_DATA_INPUT_REF]],
                    MultiInstanceLoopCharacteristicsInterface::BPMN_PROPERTY_LOOP_DATA_OUTPUT => ['1', [self::BPMN_MODEL, MultiInstanceLoopCharacteristicsInterface::BPMN_PROPERTY_LOOP_DATA_OUTPUT_REF]],
                    MultiInstanceLoopCharacteristicsInterface::BPMN_PROPERTY_INPUT_DATA_ITEM => ['1', [self::BPMN_MODEL, MultiInstanceLoopCharacteristicsInterface::BPMN_PROPERTY_INPUT_DATA_ITEM]],
                    MultiInstanceLoopCharacteristicsInterface::BPMN_PROPERTY_OUTPUT_DATA_ITEM => ['1', [self::BPMN_MODEL, MultiInstanceLoopCharacteristicsInterface::BPMN_PROPERTY_OUTPUT_DATA_ITEM]],
                ],
            ],
            'loopCardinality' => [
                FormalExpressionInterface::class,
                [
                    FormalExpressionInterface::BPMN_PROPERTY_BODY => ['1', self::DOM_ELEMENT_BODY],
                ],
            ],
            'completionCondition' => [
                FormalExpressionInterface::class,
                [
                    FormalExpressionInterface::BPMN_PROPERTY_BODY => ['1', self::DOM_ELEMENT_BODY],
                ],
            ],
            'ioSpecification' => [
                InputOutputSpecificationInterface::class,
                [
                    InputOutputSpecificationInterface::BPMN_PROPERTY_DATA_INPUT => ['n', [self::BPMN_MODEL, InputOutputSpecificationInterface::BPMN_PROPERTY_DATA_INPUT]],
                    InputOutputSpecificationInterface::BPMN_PROPERTY_DATA_OUTPUT => ['n', [self::BPMN_MODEL, InputOutputSpecificationInterface::BPMN_PROPERTY_DATA_OUTPUT]],
                    InputOutputSpecificationInterface::BPMN_PROPERTY_DATA_INPUT_SET => ['1', [self::BPMN_MODEL, InputOutputSpecificationInterface::BPMN_PROPERTY_DATA_INPUT_SET]],
                    InputOutputSpecificationInterface::BPMN_PROPERTY_DATA_OUTPUT_SET => ['1', [self::BPMN_MODEL, InputOutputSpecificationInterface::BPMN_PROPERTY_DATA_OUTPUT_SET]],
                ],
            ],
            'loopDataInputRef' => [self::IS_REFERENCE, []],
            'loopDataOutputRef' => [self::IS_REFERENCE, []],
            'inputDataItem' => [
                DataInputInterface::class,
                [
                    DataInputInterface::BPMN_PROPERTY_ITEM_SUBJECT => ['1', [self::BPMN_MODEL, DataInputInterface::BPMN_PROPERTY_ITEM_SUBJECT_REF]],
                    DataInputInterface::BPMN_PROPERTY_IS_COLLECTION => self::IS_BOOLEAN,
                ],
            ],
            'outputDataItem' => [
                DataOutputInterface::class,
                [
                    DataOutputInterface::BPMN_PROPERTY_ITEM_SUBJECT => ['1', [self::BPMN_MODEL, DataOutputInterface::BPMN_PROPERTY_ITEM_SUBJECT_REF]],
                    DataOutputInterface::BPMN_PROPERTY_IS_COLLECTION => self::IS_BOOLEAN,
                ],
            ],
            'standardLoopCharacteristics' => [
                StandardLoopCharacteristicsInterface::class,
                [
                    StandardLoopCharacteristicsInterface::BPMN_PROPERTY_TEST_BEFORE => self::IS_BOOLEAN,
                    //StandardLoopCharacteristicsInterface::BPMN_PROPERTY_LOOP_MAXIMUM => ['1', [BpmnDocument::BPMN_MODEL, StandardLoopCharacteristicsInterface::BPMN_PROPERTY_LOOP_MAXIMUM]],
                    StandardLoopCharacteristicsInterface::BPMN_PROPERTY_LOOP_CONDITION => ['1', [self::BPMN_MODEL, StandardLoopCharacteristicsInterface::BPMN_PROPERTY_LOOP_CONDITION]],
                ],
            ],
            'loopCondition' => [
                FormalExpressionInterface::class,
                [
                    FormalExpressionInterface::BPMN_PROPERTY_BODY => ['1', self::DOM_ELEMENT_BODY],
                ],
            ],
        ],
    ];

    const DOM_ELEMENT_BODY = [null, '#text'];

    const SKIP_ELEMENT = null;

    const IS_REFERENCE = 'isReference';

    const TEXT_PROPERTY = 'textProperty';

    const IS_ARRAY = 'isArray';

    const PARENT_NODE = [1, '#parent'];

    const IS_BOOLEAN = [1, '#boolean'];

    /**
     * BPMN file document constructor.
     *
     * @param string $version
     * @param string $encoding
     */
    public function __construct($version = null, $encoding = null)
    {
        parent::__construct($version, $encoding);
        $this->registerNodeClass(DOMElement::class, BpmnElement::class);
    }

    /**
     * Set the factory used to create BPMN elements.
     *
     * @param RepositoryInterface $factory
     */
    public function setFactory(RepositoryInterface $factory)
    {
        $this->factory = $factory;
    }

    /**
     * Get the factory used to create BPMN elements.
     *
     * @return \ProcessMaker\Nayra\Contracts\FactoryInterface
     */
    public function getFactory()
    {
        return $this->factory;
    }

    /**
     * Get the BPMN elements mapping.
     *
     * @return array
     */
    public function getBpmnElementsMapping()
    {
        return $this->mapping;
    }

    /**
     * Set a BPMN element mapping.
     *
     * @param string $namespace
     * @param string $tagName
     * @param mixed $mapping
     *
     * @return $this
     */
    public function setBpmnElementMapping($namespace, $tagName, $mapping)
    {
        $this->mapping[$namespace][$tagName] = $mapping;

        return $this;
    }

    /**
     * Find a element by id.
     *
     * @param string $id
     *
     * @return BpmnElement
     */
    public function findElementById($id)
    {
        $xpath = new DOMXPath($this);
        $nodes = $xpath->query("//*[@id='$id']");

        return $nodes ? $nodes->item(0) : null;
    }

    /**
     * Index a BPMN element by id.
     *
     * @param string $id
     * @param EntityInterface $bpmn
     */
    public function indexBpmnElement($id, EntityInterface $bpmn)
    {
        $this->bpmnElements[$id] = $bpmn;
    }

    /**
     * Verify if the BPMN element identified by id was previously loaded.
     *
     * @param string $id
     *
     * @return bool
     */
    public function hasBpmnInstance($id)
    {
        return isset($this->bpmnElements[$id]);
    }

    /**
     * Get a BPMN element by id.
     *
     * @param string $id
     *
     * @return \ProcessMaker\Nayra\Contracts\Bpmn\EntityInterface
     */
    public function getElementInstanceById($id)
    {
        $this->bpmnElements[$id] = isset($this->bpmnElements[$id])
            ? $this->bpmnElements[$id]
            : (
                ($element = $this->findElementById($id))
                ? $element->getBpmnElementInstance()
                : null
            );
        if ($this->bpmnElements[$id] === null) {
            throw new ElementNotFoundException($id);
        }

        return $this->bpmnElements[$id];
    }

    /**
     * Return true if the element instance exists in the Process.
     *
     * @param string $id
     *
     * @return bool
     */
    public function hasElementInstance($id)
    {
        $element = $this->findElementById($id);

        return ! empty($element) && ! empty($element->getBpmnElementInstance());
    }

    /**
     * Get skipElementsNotImplemented property.
     *
     * If set to TRUE, skip loading elements that are not implemented
     * If set to FALSE, throw ElementNotImplementedException
     *
     * @return bool
     */
    public function getSkipElementsNotImplemented()
    {
        return $this->skipElementsNotImplemented;
    }

    /**
     * Set skipElementsNotImplemented property.
     *
     * If set to TRUE, skip loading elements that are not implemented
     * If set to FALSE, throw ElementNotImplementedException
     *
     * @param bool $skipElementsNotImplemented
     *
     * @return BpmnDocument
     */
    public function setSkipElementsNotImplemented($skipElementsNotImplemented)
    {
        $this->skipElementsNotImplemented = $skipElementsNotImplemented;

        return $this;
    }

    /**
     * Get Activity instance by id.
     *
     * @param string $id
     *
     * @return \ProcessMaker\Nayra\Contracts\Bpmn\ActivityInterface
     */
    public function getActivity($id)
    {
        return $this->getElementInstanceById($id);
    }

    /**
     * Get CallActivity instance by id.
     *
     * @param string $id
     *
     * @return \ProcessMaker\Nayra\Contracts\Bpmn\CallActivityInterface
     */
    public function getCallActivity($id)
    {
        return $this->getElementInstanceById($id);
    }

    /**
     * Get CallableElement instance by id.
     *
     * @param string $id
     *
     * @return \ProcessMaker\Nayra\Contracts\Bpmn\CallableElementInterface
     */
    public function getCallableElement($id)
    {
        return $this->getElementInstanceById($id);
    }

    /**
     * Get CatchEvent instance by id.
     *
     * @param string $id
     *
     * @return \ProcessMaker\Nayra\Contracts\Bpmn\CatchEventInterface
     */
    public function getCatchEvent($id)
    {
        return $this->getElementInstanceById($id);
    }

    /**
     * Get Collaboration instance by id.
     *
     * @param string $id
     *
     * @return \ProcessMaker\Nayra\Contracts\Bpmn\CollaborationInterface
     */
    public function getCollaboration($id)
    {
        return $this->getElementInstanceById($id);
    }

    /**
     * Get DataInput instance by id.
     *
     * @param string $id
     *
     * @return \ProcessMaker\Nayra\Contracts\Bpmn\DataInputInterface
     */
    public function getDataInput($id)
    {
        return $this->getElementInstanceById($id);
    }

    /**
     * Get DataOutput instance by id.
     *
     * @param string $id
     *
     * @return \ProcessMaker\Nayra\Contracts\Bpmn\DataOutputInterface
     */
    public function getDataOutput($id)
    {
        return $this->getElementInstanceById($id);
    }

    /**
     * Get DataStore instance by id.
     *
     * @param string $id
     *
     * @return \ProcessMaker\Nayra\Contracts\Bpmn\DataStoreInterface
     */
    public function getDataStore($id)
    {
        return $this->getElementInstanceById($id);
    }

    /**
     * Get EndEvent instance by id.
     *
     * @param string $id
     *
     * @return \ProcessMaker\Nayra\Contracts\Bpmn\EndEventInterface
     */
    public function getEndEvent($id)
    {
        return $this->getElementInstanceById($id);
    }

    /**
     * Get ErrorEventDefinition instance by id.
     *
     * @param string $id
     *
     * @return \ProcessMaker\Nayra\Contracts\Bpmn\ErrorEventDefinitionInterface
     */
    public function getErrorEventDefinition($id)
    {
        return $this->getElementInstanceById($id);
    }

    /**
     * Get Error instance by id.
     *
     * @param string $id
     *
     * @return \ProcessMaker\Nayra\Contracts\Bpmn\ErrorInterface
     */
    public function getError($id)
    {
        return $this->getElementInstanceById($id);
    }

    /**
     * Get EventDefinition instance by id.
     *
     * @param string $id
     *
     * @return \ProcessMaker\Nayra\Contracts\Bpmn\EventDefinitionInterface
     */
    public function getEventDefinition($id)
    {
        return $this->getElementInstanceById($id);
    }

    /**
     * Get Event instance by id.
     *
     * @param string $id
     *
     * @return \ProcessMaker\Nayra\Contracts\Bpmn\EventInterface
     */
    public function getEvent($id)
    {
        return $this->getElementInstanceById($id);
    }

    /**
     * Get EventBasedGateway instance by id.
     *
     * @param string $id
     *
     * @return \ProcessMaker\Nayra\Contracts\Bpmn\EventBasedGatewayInterface
     */
    public function getEventBasedGateway($id)
    {
        return $this->getElementInstanceById($id);
    }

    /**
     * Get ExclusiveGateway instance by id.
     *
     * @param string $id
     *
     * @return \ProcessMaker\Nayra\Contracts\Bpmn\ExclusiveGatewayInterface
     */
    public function getExclusiveGateway($id)
    {
        return $this->getElementInstanceById($id);
    }

    /**
     * Get FlowElement instance by id.
     *
     * @param string $id
     *
     * @return \ProcessMaker\Nayra\Contracts\Bpmn\FlowElementInterface
     */
    public function getFlowElement($id)
    {
        return $this->getElementInstanceById($id);
    }

    /**
     * Get Flow instance by id.
     *
     * @param string $id
     *
     * @return \ProcessMaker\Nayra\Contracts\Bpmn\FlowInterface
     */
    public function getFlow($id)
    {
        return $this->getElementInstanceById($id);
    }

    /**
     * Get FlowNode instance by id.
     *
     * @param string $id
     *
     * @return \ProcessMaker\Nayra\Contracts\Bpmn\FlowNodeInterface
     */
    public function getFlowNode($id)
    {
        return $this->getElementInstanceById($id);
    }

    /**
     * Get FormalExpression instance by id.
     *
     * @param string $id
     *
     * @return \ProcessMaker\Nayra\Contracts\Bpmn\FormalExpressionInterface
     */
    public function getFormalExpression($id)
    {
        return $this->getElementInstanceById($id);
    }

    /**
     * Get Gateway instance by id.
     *
     * @param string $id
     *
     * @return \ProcessMaker\Nayra\Contracts\Bpmn\GatewayInterface
     */
    public function getGateway($id)
    {
        return $this->getElementInstanceById($id);
    }

    /**
     * Get InclusiveGateway instance by id.
     *
     * @param string $id
     *
     * @return \ProcessMaker\Nayra\Contracts\Bpmn\InclusiveGatewayInterface
     */
    public function getInclusiveGateway($id)
    {
        return $this->getElementInstanceById($id);
    }

    /**
     * Get InputSet instance by id.
     *
     * @param string $id
     *
     * @return \ProcessMaker\Nayra\Contracts\Bpmn\InputSetInterface
     */
    public function getInputSet($id)
    {
        return $this->getElementInstanceById($id);
    }

    /**
     * Get IntermediateCatchEvent instance by id.
     *
     * @param string $id
     *
     * @return \ProcessMaker\Nayra\Contracts\Bpmn\IntermediateCatchEventInterface
     */
    public function getIntermediateCatchEvent($id)
    {
        return $this->getElementInstanceById($id);
    }

    /**
     * Get IntermediateThrowEvent instance by id.
     *
     * @param string $id
     *
     * @return \ProcessMaker\Nayra\Contracts\Bpmn\IntermediateThrowEventInterface
     */
    public function getIntermediateThrowEvent($id)
    {
        return $this->getElementInstanceById($id);
    }

    /**
     * Get Lane instance by id.
     *
     * @param string $id
     *
     * @return \ProcessMaker\Nayra\Contracts\Bpmn\LaneInterface
     */
    public function getLane($id)
    {
        return $this->getElementInstanceById($id);
    }

    /**
     * Get LaneSet instance by id.
     *
     * @param string $id
     *
     * @return \ProcessMaker\Nayra\Contracts\Bpmn\LaneSetInterface
     */
    public function getLaneSet($id)
    {
        return $this->getElementInstanceById($id);
    }

    /**
     * Get MessageEventDefinition instance by id.
     *
     * @param string $id
     *
     * @return \ProcessMaker\Nayra\Contracts\Bpmn\MessageEventDefinitionInterface
     */
    public function getMessageEventDefinition($id)
    {
        return $this->getElementInstanceById($id);
    }

    /**
     * Get MessageFlow instance by id.
     *
     * @param string $id
     *
     * @return \ProcessMaker\Nayra\Contracts\Bpmn\MessageFlowInterface
     */
    public function getMessageFlow($id)
    {
        return $this->getElementInstanceById($id);
    }

    /**
     * Get Operation instance by id.
     *
     * @param string $id
     *
     * @return \ProcessMaker\Nayra\Contracts\Bpmn\OperationInterface
     */
    public function getOperation($id)
    {
        return $this->getElementInstanceById($id);
    }

    /**
     * Get OutputSet instance by id.
     *
     * @param string $id
     *
     * @return \ProcessMaker\Nayra\Contracts\Bpmn\OutputSetInterface
     */
    public function getOutputSet($id)
    {
        return $this->getElementInstanceById($id);
    }

    /**
     * Get ParallelGateway instance by id.
     *
     * @param string $id
     *
     * @return \ProcessMaker\Nayra\Contracts\Bpmn\ParallelGatewayInterface
     */
    public function getParallelGateway($id)
    {
        return $this->getElementInstanceById($id);
    }

    /**
     * Get Participant instance by id.
     *
     * @param string $id
     *
     * @return \ProcessMaker\Nayra\Contracts\Bpmn\ParticipantInterface
     */
    public function getParticipant($id)
    {
        return $this->getElementInstanceById($id);
    }

    /**
     * Get Process instance by id.
     *
     * @param string $id
     *
     * @return \ProcessMaker\Nayra\Contracts\Bpmn\ProcessInterface
     */
    public function getProcess($id)
    {
        return $this->getElementInstanceById($id);
    }

    /**
     * Get ScriptTask instance by id.
     *
     * @param string $id
     *
     * @return \ProcessMaker\Nayra\Contracts\Bpmn\ScriptTaskInterface
     */
    public function getScriptTask($id)
    {
        return $this->getElementInstanceById($id);
    }

    /**
     * Get ServiceTask instance by id.
     *
     * @param string $id
     *
     * @return \ProcessMaker\Nayra\Contracts\Bpmn\ServiceTaskInterface
     */
    public function getServiceTask($id)
    {
        return $this->getElementInstanceById($id);
    }

    /**
     * Get SignalEventDefinition instance by id.
     *
     * @param string $id
     *
     * @return \ProcessMaker\Nayra\Contracts\Bpmn\SignalEventDefinitionInterface
     */
    public function getSignalEventDefinition($id)
    {
        return $this->getElementInstanceById($id);
    }

    /**
     * Get StartEvent instance by id.
     *
     * @param string $id
     *
     * @return \ProcessMaker\Nayra\Contracts\Bpmn\StartEventInterface
     */
    public function getStartEvent($id)
    {
        return $this->getElementInstanceById($id);
    }

    /**
     * Get TerminateEventDefinition instance by id.
     *
     * @param string $id
     *
     * @return \ProcessMaker\Nayra\Contracts\Bpmn\TerminateEventDefinitionInterface
     */
    public function getTerminateEventDefinition($id)
    {
        return $this->getElementInstanceById($id);
    }

    /**
     * Get ThrowEvent instance by id.
     *
     * @param string $id
     *
     * @return \ProcessMaker\Nayra\Contracts\Bpmn\ThrowEventInterface
     */
    public function getThrowEvent($id)
    {
        return $this->getElementInstanceById($id);
    }

    /**
     * Get TimerEventDefinition instance by id.
     *
     * @param string $id
     *
     * @return \ProcessMaker\Nayra\Contracts\Bpmn\TimerEventDefinitionInterface
     */
    public function getTimerEventDefinition($id)
    {
        return $this->getElementInstanceById($id);
    }

    /**
     * Get ConditionalEventDefinition instance by id.
     *
     * @param string $id
     *
     * @return \ProcessMaker\Nayra\Contracts\Bpmn\ConditionalEventDefinitionInterface
     */
    public function getConditionalEventDefinition($id)
    {
        return $this->getElementInstanceById($id);
    }

    /**
     * Get BoundaryEvent instance by id.
     *
     * @param string $id
     *
     * @return \ProcessMaker\Nayra\Contracts\Bpmn\BoundaryEventInterface
     */
    public function getBoundaryEvent($id)
    {
        return $this->getElementInstanceById($id);
    }

    /**
     * Returns the document engine
     *
     * @return \ProcessMaker\Nayra\Contracts\Engine\EngineInterface
     */
    public function getEngine()
    {
        return $this->engine;
    }

    /**
     * @param \ProcessMaker\Nayra\Contracts\Engine\EngineInterface|null $engine
     *
     * @return $this
     */
    public function setEngine(EngineInterface $engine = null)
    {
        $this->engine = $engine;

        return $this;
    }

    /**
     * Validate document with BPMN schemas.
     *
     * @param string $schema
     */
    public function validateBPMNSchema($schema)
    {
        $validator = new BPMNValidator($this);
        $validation = $validator->validate($schema);
        $this->validationErrors = $validator->getErrors();

        return $validation;
    }

    /**
     * Get BPMN validation errors.
     *
     * @return array
     */
    public function getValidationErrors()
    {
        return $this->validationErrors;
    }
}
