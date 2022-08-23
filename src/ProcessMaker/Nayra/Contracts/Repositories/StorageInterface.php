<?php

namespace ProcessMaker\Nayra\Contracts\Repositories;

use ProcessMaker\Nayra\Contracts\Bpmn\EntityInterface;
use ProcessMaker\Nayra\Contracts\Engine\EngineInterface;
use ProcessMaker\Nayra\Contracts\RepositoryInterface;

/**
 * RepositoryFactory
 */
interface StorageInterface
{
    /**
     * Set the factory used to create BPMN elements.
     *
     * @param RepositoryInterface $factory
     */
    public function setFactory(RepositoryInterface $factory);

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
     * @param \ProcessMaker\Nayra\Contracts\Engine\EngineInterface|null $engine
     *
     * @return $this
     */
    public function setEngine(EngineInterface $engine = null);

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
     * @param mixed $mapping
     *
     * @return $this
     */
    public function setBpmnElementMapping($namespace, $tagName, $mapping);

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
     * @return bool
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
     * Get DataInput instance by id.
     *
     * @param string $id
     *
     * @return \ProcessMaker\Nayra\Contracts\Bpmn\DataInputInterface
     */
    public function getDataInput($id);

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
     * Get EventBasedGateway instance by id.
     *
     * @param string $id
     *
     * @return \ProcessMaker\Nayra\Contracts\Bpmn\EventBasedGatewayInterface
     */
    public function getEventBasedGateway($id);

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
     * Get ScriptTask instance by id.
     *
     * @param string $id
     *
     * @return \ProcessMaker\Nayra\Contracts\Bpmn\ScriptTaskInterface
     */
    public function getScriptTask($id);

    /**
     * Get ServiceTask instance by id.
     *
     * @param string $id
     *
     * @return \ProcessMaker\Nayra\Contracts\Bpmn\ServiceTaskInterface
     */
    public function getServiceTask($id);

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
     * Get ConditionalEventDefinition instance by id.
     *
     * @param string $id
     *
     * @return \ProcessMaker\Nayra\Contracts\Bpmn\ConditionalEventDefinitionInterface
     */
    public function getConditionalEventDefinition($id);

    /**
     * Get BoundaryEvent instance by id.
     *
     * @param string $id
     *
     * @return \ProcessMaker\Nayra\Contracts\Bpmn\BoundaryEventInterface
     */
    public function getBoundaryEvent($id);
}
