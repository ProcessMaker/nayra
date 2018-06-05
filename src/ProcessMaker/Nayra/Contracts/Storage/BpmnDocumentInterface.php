<?php

namespace ProcessMaker\Nayra\Contracts\Storage;

use ProcessMaker\Nayra\Contracts\Bpmn\EntityInterface;
use ProcessMaker\Nayra\Contracts\Engine\EngineInterface;
use ProcessMaker\Nayra\Contracts\FactoryInterface;

/**
 * BPMN DOM file interface
 *
 * @package \ProcessMaker\Nayra\Contracts\Storage
 */
interface BpmnDocumentInterface
{

    /**
     * Set the factory used to create BPMN elements.
     *
     * @param FactoryInterface $factory
     */
    public function setFactory(FactoryInterface $factory);

    /**
     * Get the factory used to create BPMN elements.
     *
     * @return \ProcessMaker\Nayra\Contracts\FactoryInterface
     */
    public function getFactory();

    /**
     * Get the document engine.
     *
     * @return \ProcessMaker\Nayra\Contracts\Engine\EngineInterface
     */
    public function getEngine();

    /**
     * Set the document engine.
     *
     * @param \ProcessMaker\Nayra\Contracts\Engine\EngineInterface $engine
     *
     * @return $this
     */
    public function setEngine(EngineInterface $engine);

    /**
     * Get the BPMN elements mapping.
     *
     * @return array
     */
    public function getBpmnElementsMapping();

    /**
     * Set a BPMN element mapping.
     *
     * @param string $namespace
     * @param string $tagName
     * @param array $mapping
     *
     * @return $this
     */
    public function setBpmnElementMapping($namespace, $tagName, array $mapping);

    /**
     * Find a element by id.
     *
     * @param string $id
     *
     * @return BpmnElement
     */
    public function findElementById($id);

    /**
     * Index a BPMN element by id.
     *
     * @param string $id
     * @param EntityInterface $bpmn
     */
    public function indexBpmnElement($id, EntityInterface $bpmn);

    /**
     * Verify if the BPMN element identified by id was previously loaded.
     *
     * @param string $id
     *
     * @return boolean
     */
    public function hasBpmnInstance($id);

    /**
     * Get a BPMN element by id.
     *
     * @param string $id
     *
     * @return \ProcessMaker\Nayra\Contracts\Bpmn\EntityInterface
     */
    public function getElementInstanceById($id);

    /**
     * Get Activity instance by id.
     *
     * @param string $id
     *
     * @return \ProcessMaker\Nayra\Contracts\Bpmn\ActivityInterface
     */
    public function getActivity($id);

    /**
     * Get CallActivity instance by id.
     *
     * @param string $id
     *
     * @return \ProcessMaker\Nayra\Contracts\Bpmn\CallActivityInterface
     */
    public function getCallActivity($id);

    /**
     * Get CallableElement instance by id.
     *
     * @param string $id
     *
     * @return \ProcessMaker\Nayra\Contracts\Bpmn\CallableElementInterface
     */
    public function getCallableElement($id);

    /**
     * Get CatchEvent instance by id.
     *
     * @param string $id
     *
     * @return \ProcessMaker\Nayra\Contracts\Bpmn\CatchEventInterface
     */
    public function getCatchEvent($id);

    /**
     * Get Collaboration instance by id.
     *
     * @param string $id
     *
     * @return \ProcessMaker\Nayra\Contracts\Bpmn\CollaborationInterface
     */
    public function getCollaboration($id);

    /**
     * Get CorrelationProperty instance by id.
     *
     * @param string $id
     *
     * @return \ProcessMaker\Nayra\Contracts\Bpmn\CorrelationPropertyInterface
     */
    public function getCorrelationProperty($id);

    /**
     * Get DataAssociation instance by id.
     *
     * @param string $id
     *
     * @return \ProcessMaker\Nayra\Contracts\Bpmn\DataAssociationInterface
     */
    public function getDataAssociation($id);

    /**
     * Get DataInputAssociation instance by id.
     *
     * @param string $id
     *
     * @return \ProcessMaker\Nayra\Contracts\Bpmn\DataInputAssociationInterface
     */
    public function getDataInputAssociation($id);

    /**
     * Get DataInput instance by id.
     *
     * @param string $id
     *
     * @return \ProcessMaker\Nayra\Contracts\Bpmn\DataInputInterface
     */
    public function getDataInput($id);

    /**
     * Get DataOutputAssociation instance by id.
     *
     * @param string $id
     *
     * @return \ProcessMaker\Nayra\Contracts\Bpmn\DataOutputAssociationInterface
     */
    public function getDataOutputAssociation($id);

    /**
     * Get DataOutput instance by id.
     *
     * @param string $id
     *
     * @return \ProcessMaker\Nayra\Contracts\Bpmn\DataOutputInterface
     */
    public function getDataOutput($id);

    /**
     * Get DataStore instance by id.
     *
     * @param string $id
     *
     * @return \ProcessMaker\Nayra\Contracts\Bpmn\DataStoreInterface
     */
    public function getDataStore($id);

    /**
     * Get EndEvent instance by id.
     *
     * @param string $id
     *
     * @return \ProcessMaker\Nayra\Contracts\Bpmn\EndEventInterface
     */
    public function getEndEvent($id);

    /**
     * Get ErrorEventDefinition instance by id.
     *
     * @param string $id
     *
     * @return \ProcessMaker\Nayra\Contracts\Bpmn\ErrorEventDefinitionInterface
     */
    public function getErrorEventDefinition($id);

    /**
     * Get Error instance by id.
     *
     * @param string $id
     *
     * @return \ProcessMaker\Nayra\Contracts\Bpmn\ErrorInterface
     */
    public function getError($id);

    /**
     * Get EventDefinition instance by id.
     *
     * @param string $id
     *
     * @return \ProcessMaker\Nayra\Contracts\Bpmn\EventDefinitionInterface
     */
    public function getEventDefinition($id);

    /**
     * Get Event instance by id.
     *
     * @param string $id
     *
     * @return \ProcessMaker\Nayra\Contracts\Bpmn\EventInterface
     */
    public function getEvent($id);

    /**
     * Get ExclusiveGateway instance by id.
     *
     * @param string $id
     *
     * @return \ProcessMaker\Nayra\Contracts\Bpmn\ExclusiveGatewayInterface
     */
    public function getExclusiveGateway($id);

    /**
     * Get FlowElement instance by id.
     *
     * @param string $id
     *
     * @return \ProcessMaker\Nayra\Contracts\Bpmn\FlowElementInterface
     */
    public function getFlowElement($id);

    /**
     * Get Flow instance by id.
     *
     * @param string $id
     *
     * @return \ProcessMaker\Nayra\Contracts\Bpmn\FlowInterface
     */
    public function getFlow($id);

    /**
     * Get FlowNode instance by id.
     *
     * @param string $id
     *
     * @return \ProcessMaker\Nayra\Contracts\Bpmn\FlowNodeInterface
     */
    public function getFlowNode($id);

    /**
     * Get FormalExpression instance by id.
     *
     * @param string $id
     *
     * @return \ProcessMaker\Nayra\Contracts\Bpmn\FormalExpressionInterface
     */
    public function getFormalExpression($id);

    /**
     * Get Gateway instance by id.
     *
     * @param string $id
     *
     * @return \ProcessMaker\Nayra\Contracts\Bpmn\GatewayInterface
     */
    public function getGateway($id);

    /**
     * Get InclusiveGateway instance by id.
     *
     * @param string $id
     *
     * @return \ProcessMaker\Nayra\Contracts\Bpmn\InclusiveGatewayInterface
     */
    public function getInclusiveGateway($id);

    /**
     * Get InputSet instance by id.
     *
     * @param string $id
     *
     * @return \ProcessMaker\Nayra\Contracts\Bpmn\InputSetInterface
     */
    public function getInputSet($id);

    /**
     * Get IntermediateCatchEvent instance by id.
     *
     * @param string $id
     *
     * @return \ProcessMaker\Nayra\Contracts\Bpmn\IntermediateCatchEventInterface
     */
    public function getIntermediateCatchEvent($id);

    /**
     * Get IntermediateThrowEvent instance by id.
     *
     * @param string $id
     *
     * @return \ProcessMaker\Nayra\Contracts\Bpmn\IntermediateThrowEventInterface
     */
    public function getIntermediateThrowEvent($id);

    /**
     * Get ItemAwareElement instance by id.
     *
     * @param string $id
     *
     * @return \ProcessMaker\Nayra\Contracts\Bpmn\ItemAwareElementInterface
     */
    public function getItemAwareElement($id);

    /**
     * Get Lane instance by id.
     *
     * @param string $id
     *
     * @return \ProcessMaker\Nayra\Contracts\Bpmn\LaneInterface
     */
    public function getLane($id);

    /**
     * Get LaneSet instance by id.
     *
     * @param string $id
     *
     * @return \ProcessMaker\Nayra\Contracts\Bpmn\LaneSetInterface
     */
    public function getLaneSet($id);

    /**
     * Get MessageEventDefinition instance by id.
     *
     * @param string $id
     *
     * @return \ProcessMaker\Nayra\Contracts\Bpmn\MessageEventDefinitionInterface
     */
    public function getMessageEventDefinition($id);

    /**
     * Get MessageFlow instance by id.
     *
     * @param string $id
     *
     * @return \ProcessMaker\Nayra\Contracts\Bpmn\MessageFlowInterface
     */
    public function getMessageFlow($id);

    /**
     * Get Operation instance by id.
     *
     * @param string $id
     *
     * @return \ProcessMaker\Nayra\Contracts\Bpmn\OperationInterface
     */
    public function getOperation($id);

    /**
     * Get OutputSet instance by id.
     *
     * @param string $id
     *
     * @return \ProcessMaker\Nayra\Contracts\Bpmn\OutputSetInterface
     */
    public function getOutputSet($id);

    /**
     * Get ParallelGateway instance by id.
     *
     * @param string $id
     *
     * @return \ProcessMaker\Nayra\Contracts\Bpmn\ParallelGatewayInterface
     */
    public function getParallelGateway($id);

    /**
     * Get Participant instance by id.
     *
     * @param string $id
     *
     * @return \ProcessMaker\Nayra\Contracts\Bpmn\ParticipantInterface
     */
    public function getParticipant($id);

    /**
     * Get Process instance by id.
     *
     * @param string $id
     *
     * @return \ProcessMaker\Nayra\Contracts\Bpmn\ProcessInterface
     */
    public function getProcess($id);

    /**
     * Get Property instance by id.
     *
     * @param string $id
     *
     * @return \ProcessMaker\Nayra\Contracts\Bpmn\PropertyInterface
     */
    public function getProperty($id);

    /**
     * Get ScriptTask instance by id.
     *
     * @param string $id
     *
     * @return \ProcessMaker\Nayra\Contracts\Bpmn\ScriptTaskInterface
     */
    public function getScriptTask($id);

    /**
     * Get Shape instance by id.
     *
     * @param string $id
     *
     * @return \ProcessMaker\Nayra\Contracts\Bpmn\ShapeInterface
     */
    public function getShape($id);

    /**
     * Get SignalEventDefinition instance by id.
     *
     * @param string $id
     *
     * @return \ProcessMaker\Nayra\Contracts\Bpmn\SignalEventDefinitionInterface
     */
    public function getSignalEventDefinition($id);

    /**
     * Get StartEvent instance by id.
     *
     * @param string $id
     *
     * @return \ProcessMaker\Nayra\Contracts\Bpmn\StartEventInterface
     */
    public function getStartEvent($id);

    /**
     * Get State instance by id.
     *
     * @param string $id
     *
     * @return \ProcessMaker\Nayra\Contracts\Bpmn\StateInterface
     */
    public function getState($id);

    /**
     * Get TerminateEventDefinition instance by id.
     *
     * @param string $id
     *
     * @return \ProcessMaker\Nayra\Contracts\Bpmn\TerminateEventDefinitionInterface
     */
    public function getTerminateEventDefinition($id);

    /**
     * Get ThrowEvent instance by id.
     *
     * @param string $id
     *
     * @return \ProcessMaker\Nayra\Contracts\Bpmn\ThrowEventInterface
     */
    public function getThrowEvent($id);

    /**
     * Get TimerEventDefinition instance by id.
     *
     * @param string $id
     *
     * @return \ProcessMaker\Nayra\Contracts\Bpmn\TimerEventDefinitionInterface
     */
    public function getTimerEventDefinition($id);

    /**
     * Get Token instance by id.
     *
     * @param string $id
     *
     * @return \ProcessMaker\Nayra\Contracts\Bpmn\TokenInterface
     */
    public function getToken($id);

    /**
     * Get Transition instance by id.
     *
     * @param string $id
     *
     * @return \ProcessMaker\Nayra\Contracts\Bpmn\TransitionInterface
     */
    public function getTransition($id);
}
