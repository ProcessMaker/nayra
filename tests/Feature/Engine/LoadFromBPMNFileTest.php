<?php

namespace Tests\Feature\Engine;

use ProcessMaker\Nayra\Contracts\Bpmn\ActivityInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\EventInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\GatewayInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\ProcessInterface;
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
        $bpmnRepository = new BpmnFileRepository();
        $bpmnRepository->setEngine($this->engine);
        $bpmnRepository->load(__DIR__ . '/files/ParallelGateway.bpmn');

        //Load a process from a bpmn repository by Id
        $process = $bpmnRepository->loadBpmElementById('ParallelGateway');

        //Create a data store with data.
        $dataStore = $bpmnRepository->getDataStoreRepository()->createDataStoreInstance();

        //Load the process
        $this->engine->createExecutionInstance($process, $dataStore);

        //Get References by id
        $start = $process->getEvents()->item(0);
        $startActivity = $bpmnRepository->loadBpmElementById('start');
        $activityA = $bpmnRepository->loadBpmElementById('ScriptTask_1');
        $activityB = $bpmnRepository->loadBpmElementById('ScriptTask_2');
        $endActivity = $bpmnRepository->loadBpmElementById('end');

        //Start the process
        $start->start();
        $this->engine->runToNextState();

        //Completes the Activity 0
        $token0 = $startActivity->getTokens($dataStore)->item(0);
        $startActivity->complete($token0);
        $this->engine->runToNextState();

        //Assertion: Verify the triggered engine events. Two activities are activated.
        $this->assertEvents([
            EventInterface::EVENT_EVENT_TRIGGERED,
            ActivityInterface::EVENT_ACTIVITY_ACTIVATED,
            ActivityInterface::EVENT_ACTIVITY_COMPLETED,
            ActivityInterface::EVENT_ACTIVITY_CLOSED,
            GatewayInterface::EVENT_GATEWAY_TOKEN_ARRIVES,
            GatewayInterface::EVENT_GATEWAY_ACTIVATED,
            GatewayInterface::EVENT_GATEWAY_TOKEN_CONSUMED,
            GatewayInterface::EVENT_GATEWAY_TOKEN_PASSED,
            ActivityInterface::EVENT_ACTIVITY_ACTIVATED,
            GatewayInterface::EVENT_GATEWAY_TOKEN_PASSED,
            ActivityInterface::EVENT_ACTIVITY_ACTIVATED
        ]);

        //Completes the Activity A
        $tokenA = $activityA->getTokens($dataStore)->item(0);
        $activityA->complete($tokenA);

        //the run to next state should go false when the max steps is reached.
        $this->assertFalse($this->engine->runToNextState(1));

        $this->engine->runToNextState();

        //Assertion: Verify the triggered engine events. The activity is closed.
        $this->assertEvents([
            ActivityInterface::EVENT_ACTIVITY_COMPLETED,
            ActivityInterface::EVENT_ACTIVITY_CLOSED,
            GatewayInterface::EVENT_GATEWAY_TOKEN_ARRIVES,
        ]);

        //Completes the Activity B
        $tokenB = $activityB->getTokens($dataStore)->item(0);
        $activityB->complete($tokenB);
        $this->engine->runToNextState();

        //Assertion: Verify the triggered engine events. The activity B is closed and process is ended.
        $this->assertEvents([
            ActivityInterface::EVENT_ACTIVITY_COMPLETED,
            ActivityInterface::EVENT_ACTIVITY_CLOSED,
            GatewayInterface::EVENT_GATEWAY_TOKEN_ARRIVES,
            GatewayInterface::EVENT_GATEWAY_ACTIVATED,
            GatewayInterface::EVENT_GATEWAY_TOKEN_CONSUMED,
            GatewayInterface::EVENT_GATEWAY_TOKEN_CONSUMED,
            GatewayInterface::EVENT_GATEWAY_TOKEN_PASSED,
            ActivityInterface::EVENT_ACTIVITY_ACTIVATED,
        ]);

        //Assertion: ActivityC has one token.
        $this->assertEquals(1, $endActivity->getTokens($dataStore)->count());

        //Completes the Activity C
        $tokenC = $endActivity->getTokens($dataStore)->item(0);
        $endActivity->complete($tokenC);
        $this->engine->runToNextState();

        //Assertion: ActivityC has no tokens.
        $this->assertEquals(0, $endActivity->getTokens($dataStore)->count());

        //Assertion: ActivityC was completed and closed, then the process has ended.
        $this->assertEvents([
            ActivityInterface::EVENT_ACTIVITY_COMPLETED,
            ActivityInterface::EVENT_ACTIVITY_CLOSED,
            EventInterface::EVENT_EVENT_TRIGGERED,
            ProcessInterface::EVENT_PROCESS_COMPLETED,
        ]);
    }

    /**
     * Test inclusive gateway loaded from BPMN file.
     *
     */
    public function testInclusiveGatewayWithDefault()
    {
        //Load a BpmnFile Repository
        $bpmnRepository = new BpmnFileRepository();
        $bpmnRepository->setEngine($this->engine);
        $bpmnRepository->load(__DIR__ . '/files/InclusiveGateway_Default.bpmn');

        //Load a process from a bpmn repository by Id
        $process = $bpmnRepository->loadBpmElementById('InclusiveGateway_Default');

        //Create a data store with data.
        $dataStore = $bpmnRepository->getDataStoreRepository()->createDataStoreInstance();
        $dataStore->putData('a', 1);
        $dataStore->putData('b', 1);

        //Get References by id
        $start = $bpmnRepository->loadBpmElementById('StartEvent');
        $startActivity = $bpmnRepository->loadBpmElementById('start');
        $activityA = $bpmnRepository->loadBpmElementById('ScriptTask_1');
        $activityB = $bpmnRepository->loadBpmElementById('ScriptTask_2');
        $default = $bpmnRepository->loadBpmElementById('ScriptTask_3');
        $endActivity = $bpmnRepository->loadBpmElementById('end');

        //Load the process
        $this->engine->createExecutionInstance($process, $dataStore);

        //Start the process
        $start->start();
        $this->engine->runToNextState();

        //Completes the Activity 0
        $token0 = $startActivity->getTokens($dataStore)->item(0);
        $startActivity->complete($token0);
        $this->engine->runToNextState();

        $this->assertEquals(0, $startActivity->getTokens($dataStore)->count());

        //Assertion: Verify the triggered engine events. Two activities are activated.
        $this->assertEvents([
            EventInterface::EVENT_EVENT_TRIGGERED,
            ActivityInterface::EVENT_ACTIVITY_ACTIVATED,
            ActivityInterface::EVENT_ACTIVITY_COMPLETED,
            ActivityInterface::EVENT_ACTIVITY_CLOSED,
            GatewayInterface::EVENT_GATEWAY_TOKEN_ARRIVES,
            GatewayInterface::EVENT_GATEWAY_ACTIVATED,
            GatewayInterface::EVENT_GATEWAY_TOKEN_CONSUMED,
            GatewayInterface::EVENT_GATEWAY_TOKEN_PASSED,
            ActivityInterface::EVENT_ACTIVITY_ACTIVATED,
            GatewayInterface::EVENT_GATEWAY_TOKEN_PASSED,
            ActivityInterface::EVENT_ACTIVITY_ACTIVATED
        ]);

        //Completes the Activity A
        $tokenA = $activityA->getTokens($dataStore)->item(0);
        $activityA->complete($tokenA);
        $this->engine->runToNextState();

        //Assertion: Verify the triggered engine events. The activity is closed.
        $this->assertEvents([
            ActivityInterface::EVENT_ACTIVITY_COMPLETED,
            ActivityInterface::EVENT_ACTIVITY_CLOSED,
            GatewayInterface::EVENT_GATEWAY_TOKEN_ARRIVES,
        ]);

        //Completes the Activity B
        $tokenB = $activityB->getTokens($dataStore)->item(0);
        $activityB->complete($tokenB);
        $this->engine->runToNextState();

        //Assertion: Verify the triggered engine events. The activity is closed and process is ended.
        $this->assertEvents([
            ActivityInterface::EVENT_ACTIVITY_COMPLETED,
            ActivityInterface::EVENT_ACTIVITY_CLOSED,
            GatewayInterface::EVENT_GATEWAY_TOKEN_ARRIVES,
            GatewayInterface::EVENT_GATEWAY_ACTIVATED,
            GatewayInterface::EVENT_GATEWAY_TOKEN_CONSUMED,
            GatewayInterface::EVENT_GATEWAY_TOKEN_CONSUMED,
            GatewayInterface::EVENT_GATEWAY_TOKEN_PASSED,
            ActivityInterface::EVENT_ACTIVITY_ACTIVATED,
        ]);

        //Completes the End Activity
        $endToken = $endActivity->getTokens($dataStore)->item(0);
        $endActivity->complete($endToken);
        $this->engine->runToNextState();

        //Assertion: Verify the activity is closed and end event is triggered.
        $this->assertEvents([
            ActivityInterface::EVENT_ACTIVITY_COMPLETED,
            ActivityInterface::EVENT_ACTIVITY_CLOSED,
            EventInterface::EVENT_EVENT_TRIGGERED,
            ProcessInterface::EVENT_PROCESS_COMPLETED,
        ]);
    }
}
