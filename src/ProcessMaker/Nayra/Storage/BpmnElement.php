<?php

namespace ProcessMaker\Nayra\Storage;

use DOMAttr;
use DOMElement;
use ProcessMaker\Nayra\Contracts\Bpmn\ActivityInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\CallActivityInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\CallableElementInterface;
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
use ProcessMaker\Nayra\Contracts\Bpmn\ParallelGatewayInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\ParticipantInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\ProcessInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\ScriptTaskInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\StartEventInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\TerminateEventDefinitionInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\TimerEventDefinitionInterface;
use ProcessMaker\Nayra\Contracts\Storage\BpmnElementInterface;

/**
 * Description of BpmnFileElement
 *
 * @property XmlLoader $ownerDocument
 *
 * @package \ProcessMaker\Nayra\Storage
 */
class BpmnElement extends DOMElement implements BpmnElementInterface
{
    const map = [
        'http://www.omg.org/spec/BPMN/20100524/MODEL' => [
            'process'      => [
                ProcessInterface::class,
                [
                    'activities' => ['n', ActivityInterface::class],
                    'gateways' => ['n', GatewayInterface::class],
                    'events' => ['n', EventInterface::class],
                    ProcessInterface::BPMN_PROPERTY_LANE_SET => ['n', [BpmnDocument::BPMN, ProcessInterface::BPMN_PROPERTY_LANE_SET]],
                ]
            ],
            'startEvent'   => [
                StartEventInterface::class,
                [
                    FlowNodeInterface::BPMN_PROPERTY_INCOMING => ['n', [BpmnDocument::BPMN, FlowNodeInterface::BPMN_PROPERTY_INCOMING]],
                    FlowNodeInterface::BPMN_PROPERTY_OUTGOING  => ['n', [BpmnDocument::BPMN, FlowNodeInterface::BPMN_PROPERTY_OUTGOING]],
                    StartEventInterface::BPMN_PROPERTY_EVENT_DEFINITIONS  => ['n', EventDefinitionInterface::class],
                ]
            ],
            'endEvent'     => [
                EndEventInterface::class,
                [
                    FlowNodeInterface::BPMN_PROPERTY_INCOMING => ['n', [BpmnDocument::BPMN, FlowNodeInterface::BPMN_PROPERTY_INCOMING]],
                    FlowNodeInterface::BPMN_PROPERTY_OUTGOING => ['n', [BpmnDocument::BPMN, FlowNodeInterface::BPMN_PROPERTY_OUTGOING]],
                    EndEventInterface::BPMN_PROPERTY_EVENT_DEFINITIONS  => ['n', EventDefinitionInterface::class],
                ]
            ],
            'task'   => [
                ActivityInterface::class,
                [
                    FlowNodeInterface::BPMN_PROPERTY_INCOMING => ['n', [BpmnDocument::BPMN, FlowNodeInterface::BPMN_PROPERTY_INCOMING]],
                    FlowNodeInterface::BPMN_PROPERTY_OUTGOING => ['n', [BpmnDocument::BPMN, FlowNodeInterface::BPMN_PROPERTY_OUTGOING]],
                ]
            ],
            'scriptTask'   => [
                ScriptTaskInterface::class,
                [
                    FlowNodeInterface::BPMN_PROPERTY_INCOMING => ['n', [BpmnDocument::BPMN, FlowNodeInterface::BPMN_PROPERTY_INCOMING]],
                    FlowNodeInterface::BPMN_PROPERTY_OUTGOING => ['n', [BpmnDocument::BPMN, FlowNodeInterface::BPMN_PROPERTY_OUTGOING]],
                ]
            ],
            FlowNodeInterface::BPMN_PROPERTY_OUTGOING     => [self::IS_PROPERTY, []],
            FlowNodeInterface::BPMN_PROPERTY_INCOMING     => [self::IS_PROPERTY, []],
            'sequenceFlow' => [
                FlowInterface::class,
                [
                    FlowInterface::BPMN_PROPERTY_SOURCE => ['1', [BpmnDocument::BPMN, FlowInterface::BPMN_PROPERTY_SOURCE_REF]],
                    FlowInterface::BPMN_PROPERTY_TARGET => ['1', [BpmnDocument::BPMN, FlowInterface::BPMN_PROPERTY_TARGET_REF]],
                    FlowInterface::BPMN_PROPERTY_CONDITION_EXPRESSION => ['1', [BpmnDocument::BPMN, 'conditionExpression']],
                ]
            ],
            'callActivity' => [
                CallActivityInterface::class,
                [
                    FlowNodeInterface::BPMN_PROPERTY_INCOMING => ['n', [BpmnDocument::BPMN, FlowNodeInterface::BPMN_PROPERTY_INCOMING]],
                    FlowNodeInterface::BPMN_PROPERTY_OUTGOING => ['n', [BpmnDocument::BPMN, FlowNodeInterface::BPMN_PROPERTY_OUTGOING]],
                    CallActivityInterface::BPMN_PROPERTY_CALLED_ELEMENT => ['1', [BpmnDocument::BPMN, CallActivityInterface::BPMN_PROPERTY_CALLED_ELEMENT]],
                ]
            ],
            'parallelGateway' => [
                ParallelGatewayInterface::class,
                [
                    FlowNodeInterface::BPMN_PROPERTY_INCOMING => ['n', [BpmnDocument::BPMN, FlowNodeInterface::BPMN_PROPERTY_INCOMING]],
                    FlowNodeInterface::BPMN_PROPERTY_OUTGOING => ['n', [BpmnDocument::BPMN, FlowNodeInterface::BPMN_PROPERTY_OUTGOING]],
                ]
            ],
            'inclusiveGateway' => [
                InclusiveGatewayInterface::class,
                [
                    FlowNodeInterface::BPMN_PROPERTY_INCOMING => ['n', [BpmnDocument::BPMN, FlowNodeInterface::BPMN_PROPERTY_INCOMING]],
                    FlowNodeInterface::BPMN_PROPERTY_OUTGOING => ['n', [BpmnDocument::BPMN, FlowNodeInterface::BPMN_PROPERTY_OUTGOING]],
                    GatewayInterface::BPMN_PROPERTY_DEFAULT => ['1', [BpmnDocument::BPMN, GatewayInterface::BPMN_PROPERTY_DEFAULT]],
                ]
            ],
            'exclusiveGateway' => [
                ExclusiveGatewayInterface::class,
                [
                    FlowNodeInterface::BPMN_PROPERTY_INCOMING => ['n', [BpmnDocument::BPMN, FlowNodeInterface::BPMN_PROPERTY_INCOMING]],
                    FlowNodeInterface::BPMN_PROPERTY_OUTGOING => ['n', [BpmnDocument::BPMN, FlowNodeInterface::BPMN_PROPERTY_OUTGOING]],
                    GatewayInterface::BPMN_PROPERTY_DEFAULT => ['1', [BpmnDocument::BPMN, GatewayInterface::BPMN_PROPERTY_DEFAULT]],
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
                    CollaborationInterface::BPMN_PROPERTY_PARTICIPANT => ['n', [BpmnDocument::BPMN, CollaborationInterface::BPMN_PROPERTY_PARTICIPANT]],
                ]
            ],
            'participant' => [
                ParticipantInterface::class,
                [
                    ParticipantInterface::BPMN_PROPERTY_PROCESS => ['1', [BpmnDocument::BPMN, ParticipantInterface::BPMN_PROPERTY_PROCESS_REF]],
                ]
            ],
            'conditionalEventDefinition' => [
                ConditionalEventDefinitionInterface::class,
                [
                    ConditionalEventDefinitionInterface::BPMN_PROPERTY_CONDITION => ['1', [BpmnDocument::BPMN, ConditionalEventDefinitionInterface::BPMN_PROPERTY_CONDITION]],
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
                    ErrorEventDefinitionInterface::BPMN_PROPERTY_ERROR => ['1', [BpmnDocument::BPMN, ErrorEventDefinitionInterface::BPMN_PROPERTY_ERROR_REF]],
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
                    MessageFlowInterface::BPMN_PROPERTY_SOURCE => ['1', [BpmnDocument::BPMN, MessageFlowInterface::BPMN_PROPERTY_SOURCE_REF]],
                    MessageFlowInterface::BPMN_PROPERTY_TARGET => ['1', [BpmnDocument::BPMN, MessageFlowInterface::BPMN_PROPERTY_TARGET_REF]],
                ]
            ],
            'timerEventDefinition' => [
                TimerEventDefinitionInterface::class,
                [
                    TimerEventDefinitionInterface::BPMN_PROPERTY_TIME_DATE => ['1', [BpmnDocument::BPMN, TimerEventDefinitionInterface::BPMN_PROPERTY_TIME_DATE]],
                    TimerEventDefinitionInterface::BPMN_PROPERTY_TIME_CYCLE => ['1', [BpmnDocument::BPMN, TimerEventDefinitionInterface::BPMN_PROPERTY_TIME_CYCLE]],
                    TimerEventDefinitionInterface::BPMN_PROPERTY_TIME_DURATION => ['1', [BpmnDocument::BPMN, TimerEventDefinitionInterface::BPMN_PROPERTY_TIME_DURATION]],
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
                    LaneSetInterface::BPMN_PROPERTY_LANE => ['n', [BpmnDocument::BPMN, LaneSetInterface::BPMN_PROPERTY_LANE]],
                ]
            ],
            'lane' => [
                LaneInterface::class,
                [
                    LaneInterface::BPMN_PROPERTY_FLOW_NODE => ['n', [BpmnDocument::BPMN, LaneInterface::BPMN_PROPERTY_FLOW_NODE_REF]],
                    LaneInterface::BPMN_PROPERTY_CHILD_LANE_SET => ['n', [BpmnDocument::BPMN, LaneInterface::BPMN_PROPERTY_CHILD_LANE_SET]],
                ]
            ],
            LaneInterface::BPMN_PROPERTY_FLOW_NODE_REF => [self::IS_PROPERTY, []],
            LaneInterface::BPMN_PROPERTY_CHILD_LANE_SET => [
                LaneSetInterface::class,
                [
                    LaneSetInterface::BPMN_PROPERTY_LANE => ['n', [BpmnDocument::BPMN, LaneSetInterface::BPMN_PROPERTY_LANE]],
                ]
            ],
        ]
    ];

    const DOM_ELEMENT_BODY = [null, '#text'];
    const SKIP_ELEMENT = null;
    const IS_PROPERTY = 'isProperty';

    /**
     * Get instance of the BPMN element.
     *
     * @return \ProcessMaker\Nayra\Contracts\Bpmn\EntityInterface
     */
    public function getBpmnElementInstance()
    {
        $id = $this->getAttribute('id');
        if ($id && $this->ownerDocument->hasBpmnInstance($id)) {
            return $this->ownerDocument->getElementInstanceById($id);
        }
        if (!array_key_exists($this->namespaceURI, static::map)) {
            throw new \Exception("Not found " . $this->namespaceURI);
        }
        if (!array_key_exists($this->localName, static::map[$this->namespaceURI])) {
            throw new \Exception("Not found " . $this->localName);
        }
        if (static::map[$this->namespaceURI][$this->localName]===self::SKIP_ELEMENT) {
            return null;
        }
        list($classInterface, $mapProperties) = static::map[$this->namespaceURI][$this->localName];
        if ($classInterface === self::IS_PROPERTY) {
            $bpmnElement = $this->ownerDocument->getElementInstanceById($this->nodeValue);
            $this->bpmn = $bpmnElement;
        } else {
            $bpmnElement = $this->ownerDocument->getFactory()->createInstanceOf($classInterface);
            $bpmnElement->setFactory($this->ownerDocument->getFactory());
            if ($bpmnElement instanceof CallableElementInterface) {
                $bpmnElement->setEngine($this->ownerDocument->getEngine());
            }
            if ($id) {
                $this->ownerDocument->indexBpmnElement($id, $bpmnElement);
            }
            foreach ($this->attributes as $attribute) {
                $this->setBpmnPropertyRef($attribute, $mapProperties, $bpmnElement);
            }
            $this->loadBodyContent($mapProperties, $bpmnElement);
            $this->loadChildElements($bpmnElement, $mapProperties);
        }
        return $bpmnElement;
    }

    /**
     * Load child elements.
     *
     * @param EntityInterface $owner
     * @param array $mapProperties
     */
    private function loadChildElements(EntityInterface $owner, array $mapProperties)
    {
        foreach ($this->childNodes as $node) {
            if (!($node instanceof BpmnElement)) {
                continue;
            }
            $bpmn = $node->getBpmnElementInstance($owner);
            if ($bpmn && is_object($bpmn)) {
                $this->setBpmnPropertyTo($bpmn, $node, $mapProperties, $owner);
            }
        }
    }

    /**
     * Set value of BPMN property.
     *
     * @param mixed $value
     * @param \ProcessMaker\Nayra\Storage\BpmnElement $node
     * @param array $mapProperties
     * @param \ProcessMaker\Nayra\Contracts\Bpmn\EntityInterface $to
     */
    private function setBpmnPropertyTo($value, BpmnElement $node, array $mapProperties, EntityInterface $to)
    {
        foreach ($mapProperties as $name => $property) {
            list($multiplicity, $type) = $property;
            $isThisProperty = (is_string($type) && is_subclass_of($value, $type))
                || (is_array($type) && $node->namespaceURI === $type[0] && $node->localName===$type[1]);
            if ($isThisProperty && $multiplicity === 'n') {
                $to->addProperty($name, $value);
            } else if ($isThisProperty && $multiplicity == '1') {
                $to->setProperty($name, $value);
            }
        }
    }

    /**
     * Set BPMN property reference.
     *
     * @param DOMAttr $node
     * @param array $mapProperties
     * @param EntityInterface $bpmnElement
     * @return void
     */
    private function setBpmnPropertyRef(DOMAttr $node, array $mapProperties, EntityInterface $bpmnElement)
    {
        foreach ($mapProperties as $name => $property) {
            list($multiplicity, $type) = $property;
            $isThisProperty = (is_array($type) && ($node->namespaceURI === $type[0] || $node->namespaceURI===null)
                && $node->localName===$type[1]);
            if ($isThisProperty && $multiplicity === 'n') {
                $ref = $this->ownerDocument->getElementInstanceById($node->value);
                $bpmnElement->addProperty($name, $ref);
                return;
            } else if ($isThisProperty && $multiplicity == '1') {
                $id = $node->value;
                $ref = $this->ownerDocument->getElementInstanceById($id);
                $setter = 'set' . $name;
                method_exists($bpmnElement, $setter) ? $bpmnElement->$setter($ref)
                    : $bpmnElement->setProperty($name, $ref);
                return;
            }
        }
        $setter = 'set' . $node->name;
        method_exists($bpmnElement, $setter) ? $bpmnElement->$setter($node->value)
            : $bpmnElement->setProperty($node->name, $node->value);
    }

    /**
     * Load body content.
     *
     * @param array $mapProperties
     * @param EntityInterface $bpmnElement
     */
    private function loadBodyContent(array $mapProperties, EntityInterface $bpmnElement)
    {
        foreach ($mapProperties as $name => $property) {
            list($multiplicity, $type) = $property;
            $isThisProperty = $type === self::DOM_ELEMENT_BODY;
            if ($isThisProperty && $multiplicity === 'n') {
                $bpmnElement->addProperty($name, $this->textContent);
            } else if ($isThisProperty && $multiplicity == '1') {
                $bpmnElement->setProperty($name, $this->textContent);
            }
        }
    }
}
