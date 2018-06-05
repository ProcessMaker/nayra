<?php

namespace ProcessMaker\Nayra\Storage;

use DOMDocument;
use DOMElement;
use DOMXPath;
use ProcessMaker\Nayra\Contracts\Bpmn\CallActivityInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\CollaborationInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\ConditionalEventDefinitionInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\EndEventInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\EntityInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\ErrorEventDefinitionInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\ErrorInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\EventDefinitionInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\EventInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\ExclusiveGatewayInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\FlowInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\FlowNodeInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\FormalExpressionInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\GatewayInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\InclusiveGatewayInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\LaneInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\LaneSetInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\MessageFlowInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\OperationInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\ParallelGatewayInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\ParticipantInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\ProcessInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\ScriptTaskInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\ServiceInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\StartEventInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\TerminateEventDefinitionInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\TimerEventDefinitionInterface;
use ProcessMaker\Nayra\Contracts\Engine\EngineInterface;
use ProcessMaker\Nayra\Contracts\FactoryInterface;
use ProcessMaker\Nayra\Contracts\Storage\BpmnDocumentInterface;

/**
 * BPMN file
 *
 * @package \ProcessMaker\Nayra\Storage
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
     * @var \ProcessMaker\Nayra\Contracts\FactoryInterface $factory
     */
    private $factory;

    private $mapping = [
        'http://www.omg.org/spec/BPMN/20100524/MODEL' => [
            'process'      => [
                ProcessInterface::class,
                [
                    'activities' => ['n', ActivityInterface::class],
                    'gateways' => ['n', GatewayInterface::class],
                    'events' => ['n', EventInterface::class],
                    ProcessInterface::BPMN_PROPERTY_LANE_SET => ['n', [BpmnDocument::BPMN_MODEL, ProcessInterface::BPMN_PROPERTY_LANE_SET]],
                ]
            ],
            'startEvent'   => [
                StartEventInterface::class,
                [
                    FlowNodeInterface::BPMN_PROPERTY_INCOMING => ['n', [BpmnDocument::BPMN_MODEL, FlowNodeInterface::BPMN_PROPERTY_INCOMING]],
                    FlowNodeInterface::BPMN_PROPERTY_OUTGOING  => ['n', [BpmnDocument::BPMN_MODEL, FlowNodeInterface::BPMN_PROPERTY_OUTGOING]],
                    StartEventInterface::BPMN_PROPERTY_EVENT_DEFINITIONS  => ['n', EventDefinitionInterface::class],
                ]
            ],
            'endEvent'     => [
                EndEventInterface::class,
                [
                    FlowNodeInterface::BPMN_PROPERTY_INCOMING => ['n', [BpmnDocument::BPMN_MODEL, FlowNodeInterface::BPMN_PROPERTY_INCOMING]],
                    FlowNodeInterface::BPMN_PROPERTY_OUTGOING => ['n', [BpmnDocument::BPMN_MODEL, FlowNodeInterface::BPMN_PROPERTY_OUTGOING]],
                    EndEventInterface::BPMN_PROPERTY_EVENT_DEFINITIONS  => ['n', EventDefinitionInterface::class],
                ]
            ],
            'task'   => [
                ActivityInterface::class,
                [
                    FlowNodeInterface::BPMN_PROPERTY_INCOMING => ['n', [BpmnDocument::BPMN_MODEL, FlowNodeInterface::BPMN_PROPERTY_INCOMING]],
                    FlowNodeInterface::BPMN_PROPERTY_OUTGOING => ['n', [BpmnDocument::BPMN_MODEL, FlowNodeInterface::BPMN_PROPERTY_OUTGOING]],
                ]
            ],
            'scriptTask'   => [
                ScriptTaskInterface::class,
                [
                    FlowNodeInterface::BPMN_PROPERTY_INCOMING => ['n', [BpmnDocument::BPMN_MODEL, FlowNodeInterface::BPMN_PROPERTY_INCOMING]],
                    FlowNodeInterface::BPMN_PROPERTY_OUTGOING => ['n', [BpmnDocument::BPMN_MODEL, FlowNodeInterface::BPMN_PROPERTY_OUTGOING]],
                ]
            ],
            FlowNodeInterface::BPMN_PROPERTY_OUTGOING     => [self::IS_PROPERTY, []],
            FlowNodeInterface::BPMN_PROPERTY_INCOMING     => [self::IS_PROPERTY, []],
            'sequenceFlow' => [
                FlowInterface::class,
                [
                    FlowInterface::BPMN_PROPERTY_SOURCE => ['1', [BpmnDocument::BPMN_MODEL, FlowInterface::BPMN_PROPERTY_SOURCE_REF]],
                    FlowInterface::BPMN_PROPERTY_TARGET => ['1', [BpmnDocument::BPMN_MODEL, FlowInterface::BPMN_PROPERTY_TARGET_REF]],
                    FlowInterface::BPMN_PROPERTY_CONDITION_EXPRESSION => ['1', [BpmnDocument::BPMN_MODEL, 'conditionExpression']],
                ]
            ],
            'callActivity' => [
                CallActivityInterface::class,
                [
                    FlowNodeInterface::BPMN_PROPERTY_INCOMING => ['n', [BpmnDocument::BPMN_MODEL, FlowNodeInterface::BPMN_PROPERTY_INCOMING]],
                    FlowNodeInterface::BPMN_PROPERTY_OUTGOING => ['n', [BpmnDocument::BPMN_MODEL, FlowNodeInterface::BPMN_PROPERTY_OUTGOING]],
                    CallActivityInterface::BPMN_PROPERTY_CALLED_ELEMENT => ['1', [BpmnDocument::BPMN_MODEL, CallActivityInterface::BPMN_PROPERTY_CALLED_ELEMENT]],
                ]
            ],
            'parallelGateway' => [
                ParallelGatewayInterface::class,
                [
                    FlowNodeInterface::BPMN_PROPERTY_INCOMING => ['n', [BpmnDocument::BPMN_MODEL, FlowNodeInterface::BPMN_PROPERTY_INCOMING]],
                    FlowNodeInterface::BPMN_PROPERTY_OUTGOING => ['n', [BpmnDocument::BPMN_MODEL, FlowNodeInterface::BPMN_PROPERTY_OUTGOING]],
                ]
            ],
            'inclusiveGateway' => [
                InclusiveGatewayInterface::class,
                [
                    FlowNodeInterface::BPMN_PROPERTY_INCOMING => ['n', [BpmnDocument::BPMN_MODEL, FlowNodeInterface::BPMN_PROPERTY_INCOMING]],
                    FlowNodeInterface::BPMN_PROPERTY_OUTGOING => ['n', [BpmnDocument::BPMN_MODEL, FlowNodeInterface::BPMN_PROPERTY_OUTGOING]],
                    GatewayInterface::BPMN_PROPERTY_DEFAULT => ['1', [BpmnDocument::BPMN_MODEL, GatewayInterface::BPMN_PROPERTY_DEFAULT]],
                ]
            ],
            'exclusiveGateway' => [
                ExclusiveGatewayInterface::class,
                [
                    FlowNodeInterface::BPMN_PROPERTY_INCOMING => ['n', [BpmnDocument::BPMN_MODEL, FlowNodeInterface::BPMN_PROPERTY_INCOMING]],
                    FlowNodeInterface::BPMN_PROPERTY_OUTGOING => ['n', [BpmnDocument::BPMN_MODEL, FlowNodeInterface::BPMN_PROPERTY_OUTGOING]],
                    GatewayInterface::BPMN_PROPERTY_DEFAULT => ['1', [BpmnDocument::BPMN_MODEL, GatewayInterface::BPMN_PROPERTY_DEFAULT]],
                ]
            ],
            'conditionExpression' => [
                FormalExpressionInterface::class,
                [
                    FormalExpressionInterface::BPMN_PROPERTY_BODY => ['1', self::DOM_ELEMENT_BODY],
                ]
            ],
            'script' => self::SKIP_ELEMENT,
            'collaboration' => [
                CollaborationInterface::class,
                [
                    CollaborationInterface::BPMN_PROPERTY_PARTICIPANT => ['n', [BpmnDocument::BPMN_MODEL, CollaborationInterface::BPMN_PROPERTY_PARTICIPANT]],
                ]
            ],
            'participant' => [
                ParticipantInterface::class,
                [
                    ParticipantInterface::BPMN_PROPERTY_PROCESS => ['1', [BpmnDocument::BPMN_MODEL, ParticipantInterface::BPMN_PROPERTY_PROCESS_REF]],
                ]
            ],
            'conditionalEventDefinition' => [
                ConditionalEventDefinitionInterface::class,
                [
                    ConditionalEventDefinitionInterface::BPMN_PROPERTY_CONDITION => ['1', [BpmnDocument::BPMN_MODEL, ConditionalEventDefinitionInterface::BPMN_PROPERTY_CONDITION]],
                ]
            ],
            'condition' => [
                FormalExpressionInterface::class,
                [
                    FormalExpressionInterface::BPMN_PROPERTY_BODY => ['1', self::DOM_ELEMENT_BODY],
                ]
            ],
            'extensionElements' => self::SKIP_ELEMENT,
            'inputSet' => self::SKIP_ELEMENT,
            'outputSet' => self::SKIP_ELEMENT,
            'terminateEventDefinition' => [
                TerminateEventDefinitionInterface::class,
                [
                ]
            ],
            'errorEventDefinition' => [
                ErrorEventDefinitionInterface::class,
                [
                    ErrorEventDefinitionInterface::BPMN_PROPERTY_ERROR => ['1', [BpmnDocument::BPMN_MODEL, ErrorEventDefinitionInterface::BPMN_PROPERTY_ERROR_REF]],
                ]
            ],
            'error' => [
                ErrorInterface::class,
                [
                ]
            ],
            'messageFlow' => [
                MessageFlowInterface::class,
                [
                    MessageFlowInterface::BPMN_PROPERTY_SOURCE => ['1', [BpmnDocument::BPMN_MODEL, MessageFlowInterface::BPMN_PROPERTY_SOURCE_REF]],
                    MessageFlowInterface::BPMN_PROPERTY_TARGET => ['1', [BpmnDocument::BPMN_MODEL, MessageFlowInterface::BPMN_PROPERTY_TARGET_REF]],
                ]
            ],
            'timerEventDefinition' => [
                TimerEventDefinitionInterface::class,
                [
                    TimerEventDefinitionInterface::BPMN_PROPERTY_TIME_DATE => ['1', [BpmnDocument::BPMN_MODEL, TimerEventDefinitionInterface::BPMN_PROPERTY_TIME_DATE]],
                    TimerEventDefinitionInterface::BPMN_PROPERTY_TIME_CYCLE => ['1', [BpmnDocument::BPMN_MODEL, TimerEventDefinitionInterface::BPMN_PROPERTY_TIME_CYCLE]],
                    TimerEventDefinitionInterface::BPMN_PROPERTY_TIME_DURATION => ['1', [BpmnDocument::BPMN_MODEL, TimerEventDefinitionInterface::BPMN_PROPERTY_TIME_DURATION]],
                ]
            ],
            TimerEventDefinitionInterface::BPMN_PROPERTY_TIME_DATE => [
                FormalExpressionInterface::class,
                [
                    FormalExpressionInterface::BPMN_PROPERTY_BODY => ['1', self::DOM_ELEMENT_BODY],
                ]
            ],
            TimerEventDefinitionInterface::BPMN_PROPERTY_TIME_CYCLE => [
                FormalExpressionInterface::class,
                [
                    FormalExpressionInterface::BPMN_PROPERTY_BODY => ['1', self::DOM_ELEMENT_BODY],
                ]
            ],
            TimerEventDefinitionInterface::BPMN_PROPERTY_TIME_DURATION => [
                FormalExpressionInterface::class,
                [
                    FormalExpressionInterface::BPMN_PROPERTY_BODY => ['1', self::DOM_ELEMENT_BODY],
                ]
            ],
            'laneSet' => [
                LaneSetInterface::class,
                [
                    LaneSetInterface::BPMN_PROPERTY_LANE => ['n', [BpmnDocument::BPMN_MODEL, LaneSetInterface::BPMN_PROPERTY_LANE]],
                ]
            ],
            'lane' => [
                LaneInterface::class,
                [
                    LaneInterface::BPMN_PROPERTY_FLOW_NODE => ['n', [BpmnDocument::BPMN_MODEL, LaneInterface::BPMN_PROPERTY_FLOW_NODE_REF]],
                    LaneInterface::BPMN_PROPERTY_CHILD_LANE_SET => ['n', [BpmnDocument::BPMN_MODEL, LaneInterface::BPMN_PROPERTY_CHILD_LANE_SET]],
                ]
            ],
            LaneInterface::BPMN_PROPERTY_FLOW_NODE_REF => [self::IS_PROPERTY, []],
            LaneInterface::BPMN_PROPERTY_CHILD_LANE_SET => [
                LaneSetInterface::class,
                [
                    LaneSetInterface::BPMN_PROPERTY_LANE => ['n', [BpmnDocument::BPMN_MODEL, LaneSetInterface::BPMN_PROPERTY_LANE]],
                ]
            ],
            'interface' => [
                ServiceInterface::class,
                [
                    ServiceInterface::BPMN_PROPERTY_OPERATIONS => ['n', [BpmnDocument::BPMN_MODEL, OperationInterface::BPMN_TAG]],
                ]
            ],
            OperationInterface::BPMN_TAG => [
                OperationInterface::class,
                [
                    OperationInterface::BPMN_PROPERTY_IN_MESSAGE => ['n', [BpmnDocument::BPMN_MODEL, OperationInterface::BPMN_PROPERTY_IN_MESSAGE]],
                    OperationInterface::BPMN_PROPERTY_OUT_MESSAGE => ['n', [BpmnDocument::BPMN_MODEL, OperationInterface::BPMN_PROPERTY_OUT_MESSAGE]],
                    OperationInterface::BPMN_PROPERTY_ERRORS => ['n', [BpmnDocument::BPMN_MODEL, OperationInterface::BPMN_PROPERTY_ERRORS]],
                ]
            ],
        ]
    ];

    const DOM_ELEMENT_BODY = [null, '#text'];
    const SKIP_ELEMENT = null;
    const IS_PROPERTY = 'isProperty';

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
     * @param FactoryInterface $factory
     */
    public function setFactory(FactoryInterface $factory)
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
     * @param array $mapping
     *
     * @return $this
     */
    public function setBpmnElementMapping($namespace, $tagName, array $mapping)
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
     * @return boolean
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
            ? $this->bpmnElements[$id] : $this->findElementById($id)->getBpmnElementInstance();
        return $this->bpmnElements[$id];
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
     * Get CorrelationProperty instance by id.
     *
     * @param string $id
     *
     * @return \ProcessMaker\Nayra\Contracts\Bpmn\CorrelationPropertyInterface
     */
    public function getCorrelationProperty($id)
    {
        return $this->getElementInstanceById($id);
    }

    /**
     * Get DataAssociation instance by id.
     *
     * @param string $id
     *
     * @return \ProcessMaker\Nayra\Contracts\Bpmn\DataAssociationInterface
     */
    public function getDataAssociation($id)
    {
        return $this->getElementInstanceById($id);
    }

    /**
     * Get DataInputAssociation instance by id.
     *
     * @param string $id
     *
     * @return \ProcessMaker\Nayra\Contracts\Bpmn\DataInputAssociationInterface
     */
    public function getDataInputAssociation($id)
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
     * Get DataOutputAssociation instance by id.
     *
     * @param string $id
     *
     * @return \ProcessMaker\Nayra\Contracts\Bpmn\DataOutputAssociationInterface
     */
    public function getDataOutputAssociation($id)
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
     * Get ItemAwareElement instance by id.
     *
     * @param string $id
     *
     * @return \ProcessMaker\Nayra\Contracts\Bpmn\ItemAwareElementInterface
     */
    public function getItemAwareElement($id)
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
     * Get Property instance by id.
     *
     * @param string $id
     *
     * @return \ProcessMaker\Nayra\Contracts\Bpmn\PropertyInterface
     */
    public function getProperty($id)
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
     * Get Shape instance by id.
     *
     * @param string $id
     *
     * @return \ProcessMaker\Nayra\Contracts\Bpmn\ShapeInterface
     */
    public function getShape($id)
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
     * Get State instance by id.
     *
     * @param string $id
     *
     * @return \ProcessMaker\Nayra\Contracts\Bpmn\StateInterface
     */
    public function getState($id)
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
     * Get Token instance by id.
     *
     * @param string $id
     *
     * @return \ProcessMaker\Nayra\Contracts\Bpmn\TokenInterface
     */
    public function getToken($id)
    {
        return $this->getElementInstanceById($id);
    }

    /**
     * Get Transition instance by id.
     *
     * @param string $id
     *
     * @return \ProcessMaker\Nayra\Contracts\Bpmn\TransitionInterface
     */
    public function getTransition($id)
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
     * @param \ProcessMaker\Nayra\Contracts\Engine\EngineInterface $engine
     *
     * @return $this
     */
    public function setEngine(EngineInterface $engine)
    {
        $this->engine = $engine;
        return $this;
    }
}
