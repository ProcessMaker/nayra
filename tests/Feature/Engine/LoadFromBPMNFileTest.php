<?php

namespace Tests\Feature\Engine;

use ProcessMaker\Nayra\Contracts\Bpmn\ActivityInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\CallableElementInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\CatchEventInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\DataInputInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\DataOutputInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\DataStoreInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\EndEventInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\ErrorInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\EventDefinitionInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\EventInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\ExclusiveGatewayInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\FlowElementInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\FlowInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\FlowNodeInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\FormalExpressionInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\GatewayInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\InclusiveGatewayInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\InputSetInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\IntermediateCatchEventInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\ItemDefinitionInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\LaneInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\LaneSetInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\MessageEventDefinitionInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\MessageFlowInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\MessageInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\OperationInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\OutputSetInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\ParallelGatewayInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\ProcessInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\ScriptTaskInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\SignalEventDefinitionInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\StartEventInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\TerminateEventDefinitionInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\ThrowEventInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\TimerEventDefinitionInterface;
use ProcessMaker\Nayra\Exceptions\ElementNotImplementedException;
use ProcessMaker\Nayra\Exceptions\NamespaceNotImplementedException;
use ProcessMaker\Nayra\Storage\BpmnDocument;

/**
 * Test load of process from BPMN files.
 *
 */
class LoadFromBPMNFileTest extends EngineTestCase
{

    /**
     * Test parallel gateway loaded from BPMN file.
     *
     */
    public function testParallelGateway()
    {
        //Load a BpmnFile Repository
        $bpmnRepository = new BpmnDocument();
        $bpmnRepository->setEngine($this->engine);
        $bpmnRepository->setFactory($this->factory);
        $bpmnRepository->load(__DIR__ . '/files/ParallelGateway.bpmn');

        //Load a process from a bpmn repository by Id
        $process = $bpmnRepository->getProcess('ParallelGateway');

        //Create a data store with data.
        $dataStore = $this->factory->createDataStore();

        //Load the process
        $instance = $this->engine->createExecutionInstance($process, $dataStore);

        //Get References by id
        $start = $process->getEvents()->item(0);
        $startActivity = $bpmnRepository->getScriptTask('start');
        $activityA = $bpmnRepository->getScriptTask('ScriptTask_1');
        $activityB = $bpmnRepository->getScriptTask('ScriptTask_2');
        $endActivity = $bpmnRepository->getScriptTask('end');

        //Start the process
        $start->start();
        $this->engine->runToNextState();

        //Completes the Activity 0
        $token0 = $startActivity->getTokens($instance)->item(0);
        $startActivity->complete($token0);
        $this->engine->runToNextState();

        //Assertion: Verify the triggered engine events. Two activities are activated.
        $this->assertEvents([
            ProcessInterface::EVENT_PROCESS_INSTANCE_CREATED,
            EventInterface::EVENT_EVENT_TRIGGERED,
            ActivityInterface::EVENT_ACTIVITY_ACTIVATED,
            ScriptTaskInterface::EVENT_SCRIPT_TASK_ACTIVATED,
            ActivityInterface::EVENT_ACTIVITY_COMPLETED,
            GatewayInterface::EVENT_GATEWAY_TOKEN_ARRIVES,
            GatewayInterface::EVENT_GATEWAY_ACTIVATED,
            GatewayInterface::EVENT_GATEWAY_TOKEN_CONSUMED,
            ActivityInterface::EVENT_ACTIVITY_CLOSED,
            GatewayInterface::EVENT_GATEWAY_TOKEN_PASSED,
            GatewayInterface::EVENT_GATEWAY_TOKEN_PASSED,
            ActivityInterface::EVENT_ACTIVITY_ACTIVATED,
            ScriptTaskInterface::EVENT_SCRIPT_TASK_ACTIVATED,
            ActivityInterface::EVENT_ACTIVITY_ACTIVATED,
            ScriptTaskInterface::EVENT_SCRIPT_TASK_ACTIVATED,
        ]);

        //Completes the Activity A
        $tokenA = $activityA->getTokens($instance)->item(0);
        $activityA->complete($tokenA);

        //the run to next state should go false when the max steps is reached.
        $this->assertFalse($this->engine->runToNextState(1));

        $this->engine->runToNextState();

        //Assertion: Verify the triggered engine events. The activity is closed.
        $this->assertEvents([
            ActivityInterface::EVENT_ACTIVITY_COMPLETED,
            GatewayInterface::EVENT_GATEWAY_TOKEN_ARRIVES,
        ]);

        //Completes the Activity B
        $tokenB = $activityB->getTokens($instance)->item(0);
        $activityB->complete($tokenB);
        $this->engine->runToNextState();

        //Assertion: Verify the triggered engine events. The activity B is closed and process is ended.
        $this->assertEvents([
            ActivityInterface::EVENT_ACTIVITY_COMPLETED,
            GatewayInterface::EVENT_GATEWAY_TOKEN_ARRIVES,
            GatewayInterface::EVENT_GATEWAY_ACTIVATED,
            GatewayInterface::EVENT_GATEWAY_TOKEN_CONSUMED,
            ActivityInterface::EVENT_ACTIVITY_CLOSED,
            GatewayInterface::EVENT_GATEWAY_TOKEN_CONSUMED,
            ActivityInterface::EVENT_ACTIVITY_CLOSED,
            GatewayInterface::EVENT_GATEWAY_TOKEN_PASSED,
            ActivityInterface::EVENT_ACTIVITY_ACTIVATED,
            ScriptTaskInterface::EVENT_SCRIPT_TASK_ACTIVATED,
        ]);

        //Assertion: ActivityC has one token.
        $this->assertEquals(1, $endActivity->getTokens($instance)->count());

        //Completes the Activity C
        $tokenC = $endActivity->getTokens($instance)->item(0);
        $endActivity->complete($tokenC);
        $this->engine->runToNextState();

        //Assertion: ActivityC has no tokens.
        $this->assertEquals(0, $endActivity->getTokens($instance)->count());

        //Assertion: ActivityC was completed and closed, then the process has ended.
        $this->assertEvents([
            ActivityInterface::EVENT_ACTIVITY_COMPLETED,
            ActivityInterface::EVENT_ACTIVITY_CLOSED,
            EndEventInterface::EVENT_THROW_TOKEN_ARRIVES,
            EndEventInterface::EVENT_THROW_TOKEN_CONSUMED,
            EndEventInterface::EVENT_EVENT_TRIGGERED,
            ProcessInterface::EVENT_PROCESS_INSTANCE_COMPLETED,
        ]);
    }

    /**
     * Test inclusive gateway loaded from BPMN file.
     *
     */
    public function t1estInclusiveGatewayWithDefault()
    {
        //Load a BpmnFile Repository
        $bpmnRepository = new BpmnDocument();
        $bpmnRepository->setEngine($this->engine);
        $bpmnRepository->setFactory($this->factory);
        $bpmnRepository->load(__DIR__ . '/files/InclusiveGateway_Default.bpmn');

        //Load a process from a bpmn repository by Id
        $process = $bpmnRepository->getProcess('InclusiveGateway_Default');

        //Create a data store with data.
        $dataStore = $this->factory->createDataStore();
        $dataStore->putData('a', 1);
        $dataStore->putData('b', 1);

        //Get References by id
        $start = $bpmnRepository->getStartEvent('StartEvent');
        $startActivity = $bpmnRepository->getScriptTask('start');
        $activityA = $bpmnRepository->getScriptTask('ScriptTask_1');
        $activityB = $bpmnRepository->getScriptTask('ScriptTask_2');
        $default = $bpmnRepository->getScriptTask('ScriptTask_3');
        $endActivity = $bpmnRepository->getEndEvent('end');

        //Load the process
        $instance = $this->engine->createExecutionInstance($process, $dataStore);

        //Start the process
        $start->start();
        $this->engine->runToNextState();

        //Completes the Activity 0
        $token0 = $startActivity->getTokens($instance)->item(0);
        $startActivity->complete($token0);
        $this->engine->runToNextState();

        $this->assertEquals(0, $startActivity->getTokens($instance)->count());

        //Assertion: Verify the triggered engine events. Two activities are activated.
        $this->assertEvents([
            ProcessInterface::EVENT_PROCESS_INSTANCE_CREATED,
            EventInterface::EVENT_EVENT_TRIGGERED,
            ActivityInterface::EVENT_ACTIVITY_ACTIVATED,
            ScriptTaskInterface::EVENT_SCRIPT_TASK_ACTIVATED,
            ActivityInterface::EVENT_ACTIVITY_COMPLETED,
            GatewayInterface::EVENT_GATEWAY_TOKEN_ARRIVES,
            GatewayInterface::EVENT_GATEWAY_ACTIVATED,
            GatewayInterface::EVENT_GATEWAY_TOKEN_CONSUMED,
            ActivityInterface::EVENT_ACTIVITY_CLOSED,
            GatewayInterface::EVENT_GATEWAY_TOKEN_PASSED,
            GatewayInterface::EVENT_GATEWAY_TOKEN_PASSED,
            ActivityInterface::EVENT_ACTIVITY_ACTIVATED,
            ScriptTaskInterface::EVENT_SCRIPT_TASK_ACTIVATED,
            ActivityInterface::EVENT_ACTIVITY_ACTIVATED,
            ScriptTaskInterface::EVENT_SCRIPT_TASK_ACTIVATED,
        ]);

        //Completes the Activity A
        $tokenA = $activityA->getTokens($instance)->item(0);
        $activityA->complete($tokenA);
        $this->engine->runToNextState();

        //Assertion: Verify the triggered engine events. The activity is closed.
        $this->assertEvents([
            ActivityInterface::EVENT_ACTIVITY_COMPLETED,
            GatewayInterface::EVENT_GATEWAY_TOKEN_ARRIVES,
        ]);

        //Completes the Activity B
        $tokenB = $activityB->getTokens($instance)->item(0);
        $activityB->complete($tokenB);
        $this->engine->runToNextState();

        //Assertion: Verify the triggered engine events. The activity is closed and process is ended.
        $this->assertEvents([
            ActivityInterface::EVENT_ACTIVITY_COMPLETED,
            GatewayInterface::EVENT_GATEWAY_TOKEN_ARRIVES,
            GatewayInterface::EVENT_GATEWAY_ACTIVATED,
            GatewayInterface::EVENT_GATEWAY_TOKEN_CONSUMED,
            ActivityInterface::EVENT_ACTIVITY_CLOSED,
            GatewayInterface::EVENT_GATEWAY_TOKEN_CONSUMED,
            ActivityInterface::EVENT_ACTIVITY_CLOSED,
            GatewayInterface::EVENT_GATEWAY_TOKEN_PASSED,
            ActivityInterface::EVENT_ACTIVITY_ACTIVATED,
            ScriptTaskInterface::EVENT_SCRIPT_TASK_ACTIVATED,
        ]);

        //Completes the End Activity
        $endToken = $endActivity->getTokens($instance)->item(0);
        $endActivity->complete($endToken);
        $this->engine->runToNextState();

        //Assertion: Verify the activity is closed and end event is triggered.
        $this->assertEvents([
            ActivityInterface::EVENT_ACTIVITY_COMPLETED,
            ActivityInterface::EVENT_ACTIVITY_CLOSED,
            EndEventInterface::EVENT_THROW_TOKEN_ARRIVES,
            EndEventInterface::EVENT_THROW_TOKEN_CONSUMED,
            EndEventInterface::EVENT_EVENT_TRIGGERED,
            ProcessInterface::EVENT_PROCESS_INSTANCE_COMPLETED,
        ]);
    }

    /**
     * Test to load a collaboration.
     *
     */
    public function testLoadCollaborationWithMultipleProcesses()
    {
        //Load a BpmnFile Repository
        $bpmnRepository = new BpmnDocument();
        $bpmnRepository->setEngine($this->engine);
        $bpmnRepository->setFactory($this->factory);
        $bpmnRepository->load(__DIR__ . '/files/LoadBPMNElements.bpmn');

        //Get a Collaboration
        $collaboration = $bpmnRepository->getCollaboration('COLLABORATION_1');
        //Assertion: Verify the number of message flows in the Collaboration
        $this->assertEquals(1, $collaboration->getMessageFlows()->count());
        //Assertion: Verify the number of participants in the Collaboration
        $this->assertEquals(3, $collaboration->getParticipants()->count());

        //Get the first process
        $processOne = $bpmnRepository->getProcess('PROCESS_1');
        //Assertion: Verify the number of activities in the process one
        $this->assertEquals(7, $processOne->getActivities()->count());
        //Assertion: Verify the number of gateways in the process one
        $this->assertEquals(2, $processOne->getGateways()->count());
        //Assertion: Verify the number of gateways in the process one
        $this->assertEquals(5, $processOne->getEvents()->count());

        //Get the intermediate throw event
        $throwEvent = $bpmnRepository->getIntermediateThrowEvent('_20');
        //Assertion: Verify the number of event definitions
        $this->assertEquals(1, $throwEvent->getEventDefinitions()->count());
        //Assertion: Verify it is an MessageEventDefinitionInterface
        $eventDefinition = $throwEvent->getEventDefinitions()->item(0);
        $this->assertInstanceOf(MessageEventDefinitionInterface::class, $eventDefinition);
        //Assertion: Verify the payload is a Message
        $this->assertInstanceOf(MessageInterface::class, $eventDefinition->getPayload());
        //Assertion: Verify the operation
        $operation = $eventDefinition->getOperation();
        $this->assertInstanceOf(OperationInterface::class, $operation);
        //Assertion: Verify the operation implementation
        $this->assertEquals('foo.service.url', $operation->getImplementation());
        //Assertion: Verify the input message of the operation.
        $this->assertInstanceOf(MessageInterface::class, $operation->getInMessage()->item(0));
        //Assertion: Verify the output message of the operation.
        $this->assertInstanceOf(MessageInterface::class, $operation->getOutMessage()->item(0));
        //Assertion: Verify the output message of the operation.
        $error = $operation->getErrors()->item(0);
        $this->assertInstanceOf(ErrorInterface::class, $error);
        //Assertion: Verify error name.
        $this->assertEquals('BusinessError', $error->getName());
        //Assertion: Verify error code.
        $this->assertEquals('4040', $error->getErrorCode());

        //Get the third process as callable element
        $processThree = $bpmnRepository->getCallableElement('PROCESS_3');
        //Assertion: Verify it is a callable element.
        $this->assertInstanceOf(CallableElementInterface::class, $processThree);

        //Get a catch event element
        $element = $bpmnRepository->getCatchEvent('_50');
        //Assertion: Verify it is a catch event.
        $this->assertInstanceOf(CatchEventInterface::class, $element);

        //Get a data input element
        $element = $bpmnRepository->getDataInput('Din_20_1');
        //Assertion: Verify it is a data input.
        $this->assertInstanceOf(DataInputInterface::class, $element);
        $this->assertInstanceOf(ItemDefinitionInterface::class, $element->getItemSubject());

        //Get a data input collection element
        $element = $bpmnRepository->getDataInput('Din_20_2');
        //Assertion: Verify it is a data input.
        $this->assertInstanceOf(DataInputInterface::class, $element);
        $this->assertEquals(true, $element->isCollection());

        //Get a data output element
        $element = $bpmnRepository->getDataOutput('Dout_22_1');
        //Assertion: Verify it is a data output.
        $this->assertInstanceOf(DataOutputInterface::class, $element);
        $this->assertInstanceOf(ItemDefinitionInterface::class, $element->getItemSubject());

        //Get a data output collection element
        $element = $bpmnRepository->getDataOutput('Dout_22_2');
        //Assertion: Verify it is a data output.
        $this->assertInstanceOf(DataOutputInterface::class, $element);
        $this->assertEquals(true, $element->isCollection());

        //Get a data store element
        $element = $bpmnRepository->getDataStore('DS_1');
        //Assertion: Verify it is a data store.
        $this->assertInstanceOf(DataStoreInterface::class, $element);

        //Get a event definition element
        $element = $bpmnRepository->getEventDefinition('_4_ED_1');
        //Assertion: Verify it is a event definition.
        $this->assertInstanceOf(EventDefinitionInterface::class, $element);

        //Get a event element
        $element = $bpmnRepository->getEvent('_50');
        //Assertion: Verify it is a event.
        $this->assertInstanceOf(EventInterface::class, $element);

        //Get a exclusive gateway element
        $element = $bpmnRepository->getExclusiveGateway('_15');
        //Assertion: Verify it is a exclusive gateway.
        $this->assertInstanceOf(ExclusiveGatewayInterface::class, $element);

        //Get a flow element element
        $element = $bpmnRepository->getFlowElement('_50');
        //Assertion: Verify it is a flow element.
        $this->assertInstanceOf(FlowElementInterface::class, $element);

        //Get a flow element
        $element = $bpmnRepository->getFlow('_10');
        //Assertion: Verify it is a flow.
        $this->assertInstanceOf(FlowInterface::class, $element);

        //Get a flow node element
        $element = $bpmnRepository->getFlowNode('_50');
        //Assertion: Verify it is a flow node.
        $this->assertInstanceOf(FlowNodeInterface::class, $element);

        //Get a formal expression element
        $element = $bpmnRepository->getFormalExpression('TD_EX_1');
        //Assertion: Verify it is a formal expression.
        $this->assertInstanceOf(FormalExpressionInterface::class, $element);

        //Get a gateway element
        $element = $bpmnRepository->getGateway('_30');
        //Assertion: Verify it is a gateway.
        $this->assertInstanceOf(GatewayInterface::class, $element);

        //Get a inclusive gateway element
        $element = $bpmnRepository->getInclusiveGateway('_30');
        //Assertion: Verify it is a inclusive gateway.
        $this->assertInstanceOf(InclusiveGatewayInterface::class, $element);

        //Get a input set element
        $element = $bpmnRepository->getInputSet('IS_1');
        //Assertion: Verify it is a input set.
        $this->assertInstanceOf(InputSetInterface::class, $element);
        //Assertion: Verify the input set content.
        $this->assertEquals(2, $element->getDataInputs()->count());

        //Get a intermediate catch event element
        $element = $bpmnRepository->getIntermediateCatchEvent('_50');
        //Assertion: Verify it is a intermediate catch event.
        $this->assertInstanceOf(IntermediateCatchEventInterface::class, $element);

        //Get a lane element
        $element = $bpmnRepository->getLane('_59');
        //Assertion: Verify it is a lane.
        $this->assertInstanceOf(LaneInterface::class, $element);

        //Get a lane set element
        $element = $bpmnRepository->getLaneSet('LS_59');
        //Assertion: Verify it is a lane set.
        $this->assertInstanceOf(LaneSetInterface::class, $element);

        //Get a message event definition element
        $element = $bpmnRepository->getMessageEventDefinition('_22_ED_1');
        //Assertion: Verify it is a message event definition.
        $this->assertInstanceOf(MessageEventDefinitionInterface::class, $element);

        //Get a message flow element
        $element = $bpmnRepository->getMessageFlow('_71');
        //Assertion: Verify it is a message flow.
        $this->assertInstanceOf(MessageFlowInterface::class, $element);

        //Get a operation element
        $element = $bpmnRepository->getOperation('IF_1_O_1');
        //Assertion: Verify it is a operation.
        $this->assertInstanceOf(OperationInterface::class, $element);

        //Get a output set element
        $element = $bpmnRepository->getOutputSet('OS_1');
        //Assertion: Verify it is a output set.
        $this->assertInstanceOf(OutputSetInterface::class, $element);
        //Assertion: Verify the output set content.
        $this->assertEquals(2, $element->getDataOutputs()->count());

        //Get a parallel gateway element
        $element = $bpmnRepository->getParallelGateway('_38');
        //Assertion: Verify it is a parallel gateway.
        $this->assertInstanceOf(ParallelGatewayInterface::class, $element);

        //Get a signal event definition element
        $element = $bpmnRepository->getSignalEventDefinition('_50_ED_1');
        //Assertion: Verify it is a signal event definition.
        $this->assertInstanceOf(SignalEventDefinitionInterface::class, $element);

        //Get a terminate event definition element
        $element = $bpmnRepository->getTerminateEventDefinition('_53_ED_1');
        //Assertion: Verify it is a terminate event definition.
        $this->assertInstanceOf(TerminateEventDefinitionInterface::class, $element);

        //Get a throw event element
        $element = $bpmnRepository->getThrowEvent('_20');
        //Assertion: Verify it is a throw event.
        $this->assertInstanceOf(ThrowEventInterface::class, $element);

        //Get a timer event definition element
        $element = $bpmnRepository->getTimerEventDefinition('_72_ED_1');
        //Assertion: Verify it is a timer event definition.
        $this->assertInstanceOf(TimerEventDefinitionInterface::class, $element);
    }

    /**
     * Test to set a custom element mapping.
     *
     */
    public function testUseACustomElementMapping()
    {
        //Load a BpmnFile Repository
        $bpmnRepository = new BpmnDocument();
        $bpmnRepository->setEngine($this->engine);
        $bpmnRepository->setFactory($this->factory);
        $bpmnRepository->setBpmnElementMapping('http://www.processmaker.org/spec/PM/20100607/MODEL', 'webEntry', [
                StartEventInterface::class,
                [
                    FlowNodeInterface::BPMN_PROPERTY_INCOMING => ['n', [BpmnDocument::BPMN_MODEL, FlowNodeInterface::BPMN_PROPERTY_INCOMING]],
                    FlowNodeInterface::BPMN_PROPERTY_OUTGOING  => ['n', [BpmnDocument::BPMN_MODEL, FlowNodeInterface::BPMN_PROPERTY_OUTGOING]],
                    StartEventInterface::BPMN_PROPERTY_EVENT_DEFINITIONS  => ['n', EventDefinitionInterface::class],
                ]
            ]);
        $bpmnRepository->load(__DIR__ . '/files/CustomElements.bpmn');
        $task = $bpmnRepository->getActivity('_2');
        $this->assertEquals('Web Entry', $task->getName());
    }

    /**
     * Test to load custom elements.
     *
     */
    public function testCustomNameSpaceNotImplemented()
    {
        //Load a BpmnFile Repository
        $bpmnRepository = new BpmnDocument();
        $bpmnRepository->setEngine($this->engine);
        $bpmnRepository->setFactory($this->factory);
        $bpmnRepository->load(__DIR__ . '/files/CustomElements.bpmn');
        $this->expectException(NamespaceNotImplementedException::class);
        $bpmnRepository->getActivity('_2');
    }

    /**
     * Test to load custom elements.
     *
     */
    public function testCustomElementNotImplemented()
    {
        //Load a BpmnFile Repository
        $bpmnRepository = new BpmnDocument();
        $bpmnRepository->setEngine($this->engine);
        $bpmnRepository->setFactory($this->factory);
        $bpmnRepository->setBpmnElementMapping('http://www.processmaker.org/spec/PM/20100607/MODEL', 'task', [
                ActivityInterface::class,
                [
                    FlowNodeInterface::BPMN_PROPERTY_INCOMING => ['n', [BpmnDocument::BPMN_MODEL, FlowNodeInterface::BPMN_PROPERTY_INCOMING]],
                    FlowNodeInterface::BPMN_PROPERTY_OUTGOING => ['n', [BpmnDocument::BPMN_MODEL, FlowNodeInterface::BPMN_PROPERTY_OUTGOING]],
                ]
            ]);
        $bpmnRepository->load(__DIR__ . '/files/CustomElements.bpmn');
        $this->expectException(ElementNotImplementedException::class);
        $bpmnRepository->getActivity('_2');
    }
}
