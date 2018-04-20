<?php

namespace ProcessMaker\Repositories;

use DOMAttr;
use DOMElement;
use ProcessMaker\Nayra\Contracts\Bpmn\ActivityInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\EntityInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\EventInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\GatewayInterface;

/**
 * Description of BpmnFileElement
 *
 * @property BpmnFileRepository $ownerDocument
 */
class BpmnFileElement extends DOMElement
{
    const map = [
        'http://www.omg.org/spec/BPMN/20100524/MODEL' => [
            'process'      => [
                'getProcessRepository',
                'createProcessInstance',
                [
                    'activities' => ['n', ActivityInterface::class],
                    'gateways' => ['n', GatewayInterface::class],
                    'events' => ['n', EventInterface::class],
                ]
            ],
            'startEvent'   => [
                'getEventRepository',
                'createStartEventInstance',
                [
                    'outgoing'  => ['n', [BpmnFileRepository::BPMN, 'outgoing']],
                ]
            ],
            'endEvent'     => [
                'getEventRepository',
                'createEndEventInstance',
                [
                    'outgoing' => ['n', [BpmnFileRepository::BPMN, 'outgoing']],
                ]
            ],
            'scriptTask'   => [
                'getActivityRepository',
                'createActivityInstance',
                [
                    'outgoing' => ['n', [BpmnFileRepository::BPMN, 'outgoing']],
                ]
            ],
            'outgoing'     => ['property', '', []],
            'incoming'     => ['property', '', []],
            'sequenceFlow' => [
                'getFlowRepository',
                'createFlowInstance',
                [
                    'source' => ['1', [BpmnFileRepository::BPMN, 'sourceRef']],
                    'target' => ['1', [BpmnFileRepository::BPMN, 'targetRef']],
                    'CONDITION' => ['1', [BpmnFileRepository::BPMN, 'conditionExpression']],
                ]
            ],
            'callActivity' => [
                'getActivityRepository',
                'createCallActivityInstance',
                [
                    'outgoing' => ['n', [BpmnFileRepository::BPMN, 'outgoing']],
                ]
            ],
            'parallelGateway' => [
                'getGatewayRepository',
                'createParallelGatewayInstance',
                [
                    'incoming' => ['n', [BpmnFileRepository::BPMN, 'incoming']],
                    'outgoing' => ['n', [BpmnFileRepository::BPMN, 'outgoing']],
                ]
            ],
            'inclusiveGateway' => [
                'getGatewayRepository',
                'createInclusiveGatewayInstance',
                [
                    'incoming' => ['n', [BpmnFileRepository::BPMN, 'incoming']],
                    'outgoing' => ['n', [BpmnFileRepository::BPMN, 'outgoing']],
                    'default' => ['1', [BpmnFileRepository::BPMN, 'default']],
                ]
            ],
            'conditionExpression' => [
                'getRootElementRepository',
                'createFormalExpressionInstance',
                [
                    'body'            => ['1', self::DOM_ELEMENT_BODY],
                ]
            ],
            'script' => self::SKIP_ELEMENT
        ]
    ];

    const DOM_ELEMENT_BODY = [null, '#text'];
    const SKIP_ELEMENT = null;

    /**
     * Get a bpmn element from a dom element.
     *
     * @return \ProcessMaker\Nayra\Contracts\Bpmn\EntityInterface
     */
    public function getBpmn()
    {
        $id = $this->getAttribute('id');
        if ($id && $this->ownerDocument->hasBpmnInstance($id)) {
            return $this->ownerDocument->loadBpmElementById($id);
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
        list($repository, $method, $mapProperties) = static::map[$this->namespaceURI][$this->localName];
        if ($repository === 'property') {
            $bpmnElement = $this->ownerDocument->loadBpmElementById($this->nodeValue);
            $this->bpmn = $bpmnElement;
        } else {
            $bpmnElement = $this->ownerDocument->$repository()->$method();
            if ($id) {
                $this->ownerDocument->setBpmnElementById($id, $bpmnElement);
            }
            $properties = [];
            foreach ($this->attributes as $attribute) {
                $properties[$attribute->name] = $attribute->value;
            }
            $bpmnElement->loadCustomProperties($properties);
            foreach ($this->attributes as $attribute) {
                $this->setBpmnPropertyRef($attribute, $mapProperties, $bpmnElement);
            }
            $this->setBpmnBody($mapProperties, $bpmnElement);
            $this->loadChildElements($bpmnElement, $mapProperties);
        }
        return $bpmnElement;
    }

    private function loadChildElements(EntityInterface $owner, $mapProperties)
    {
        foreach ($this->childNodes as $node) {
            if (!($node instanceof BpmnFileElement)) {
                continue;
            }
            $bpmn = $node->getBpmn($owner);
            if ($bpmn && is_object($bpmn)) {
                $this->setBpmnPropertyTo($bpmn, $node, $mapProperties, $owner);
            }
        }
    }

    private function setBpmnPropertyTo($value, BpmnFileElement $node, $mapProperties, EntityInterface $to)
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

    private function setBpmnPropertyRef(DOMAttr $node, $mapProperties, EntityInterface $bpmnElement)
    {
        foreach ($mapProperties as $name => $property) {
            list($multiplicity, $type) = $property;
            $isThisProperty = (is_array($type) && ($node->namespaceURI === $type[0] || $node->namespaceURI===null)
                && $node->localName===$type[1]);
            if ($isThisProperty && $multiplicity === 'n') {
                $ref = $this->ownerDocument->loadBpmElementById($node->value);
                $bpmnElement->addProperty($name, $ref);
            } else if ($isThisProperty && $multiplicity == '1') {
                $id = $node->value;
                $ref = $this->ownerDocument->loadBpmElementById($id);
                $bpmnElement->setProperty($name, $ref);
            }
        }
    }

    private function setBpmnBody($mapProperties, EntityInterface $bpmnElement)
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
