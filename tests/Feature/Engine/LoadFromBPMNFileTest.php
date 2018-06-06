<?php

namespace Tests\Feature\Engine;

use ProcessMaker\Nayra\Contracts\Bpmn\ActivityInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\DataStoreInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\EndEventInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\EventInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\GatewayInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\ProcessInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\ScriptTaskInterface;
use ProcessMaker\Nayra\Storage\BpmnDocument;
use ProcessMaker\Repositories\BpmnFileRepository;

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
        $dataStore = $this->factory->createInstanceOf(DataStoreInterface::class);

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
    public function testInclusiveGatewayWithDefault()
    {
        //Load a BpmnFile Repository
        $bpmnRepository = new BpmnDocument();
        $bpmnRepository->setEngine($this->engine);
        $bpmnRepository->setFactory($this->factory);
        $bpmnRepository->load(__DIR__ . '/files/InclusiveGateway_Default.bpmn');

        //Load a process from a bpmn repository by Id
        $process = $bpmnRepository->getProcess('InclusiveGateway_Default');

        //Create a data store with data.
        $dataStore = $this->factory->createInstanceOf(DataStoreInterface::class);
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
    public function t1estLoadCollaborationWithMultipleProcesses()
    {
        //Load a BpmnFile Repository
        $bpmnRepository = new BpmnDocument();
        $bpmnRepository->setEngine($this->engine);
        $bpmnRepository->setFactory($this->factory);
        $bpmnRepository->load(__DIR__ . '/files/LoadBPMNElements.bpmn');

        //Load a process from a bpmn repository by Id
        $collaboration = $bpmnRepository->getCollaboration('COLLABORATION_1');
    }
}
