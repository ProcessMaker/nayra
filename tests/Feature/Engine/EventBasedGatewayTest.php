<?php

namespace Tests\Feature\Engine;

use ProcessMaker\Nayra\Contracts\Bpmn\ActivityInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\CatchEventInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\EventInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\GatewayInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\IntermediateCatchEventInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\ProcessInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\ThrowEventInterface;
use ProcessMaker\Nayra\Contracts\Engine\ExecutionInstanceInterface;
use ProcessMaker\Nayra\Storage\BpmnDocument;

/**
 * Test the event based gateway element.
 *
 */
class EventBasedGatewayTest extends EngineTestCase
{
    /**
     * Current instance
     *
     * @var ExecutionInstanceInterface
     */
    private $instance;

    /**
     * Test the WCP16 Deferred Choice pattern
     *
     */
    public function testDeferredChoiceChoiceOne()
    {
        // Load a BpmnFile Repository
        $bpmnRepository = new BpmnDocument();
        $bpmnRepository->setEngine($this->engine);
        $bpmnRepository->setFactory($this->repository);
        $bpmnRepository->load(__DIR__ . '/files/WCP16_DeferredChoiceEBG.bpmn');

        // Load the collaboration
        $bpmnRepository->getProcess('COLLABORATION_1');

        // Load a process from a bpmn repository by Id
        $process = $bpmnRepository->getProcess('PROCESS_1');
        $managerProcess = $bpmnRepository->getProcess('PROCESS_2');

        //Get start event and event definition references
        $startTask = $bpmnRepository->getScriptTask('_5');
        $approve = $bpmnRepository->getScriptTask('_17');
        $catch1 = $bpmnRepository->getIntermediateCatchEvent('_25');
        $catch2 = $bpmnRepository->getIntermediateCatchEvent('_27');

        $task2 = $bpmnRepository->getScriptTask('_29');
        $task3 = $bpmnRepository->getScriptTask('_31');
        //$timer = $bpmnRepository->getIntermediateCatchEvent('IntermediateCatchEvent_Timer');

        // Start the process
        $this->instance = $process->call();
        $this->engine->runToNextState();

        // Complete the 'start' activity
        $this->completeTask($startTask);

        // Assertion: Verify that Error End Event was triggered and the process is completed
        $this->assertEvents([
            ProcessInterface::EVENT_PROCESS_INSTANCE_CREATED,
            EventInterface::EVENT_EVENT_TRIGGERED,
            ActivityInterface::EVENT_ACTIVITY_ACTIVATED,
            ActivityInterface::EVENT_ACTIVITY_COMPLETED,
            GatewayInterface::EVENT_GATEWAY_TOKEN_ARRIVES,
            GatewayInterface::EVENT_GATEWAY_ACTIVATED,
            GatewayInterface::EVENT_GATEWAY_TOKEN_CONSUMED,
            ActivityInterface::EVENT_ACTIVITY_CLOSED,
            GatewayInterface::EVENT_GATEWAY_TOKEN_PASSED,
            GatewayInterface::EVENT_GATEWAY_TOKEN_ARRIVES,
            GatewayInterface::EVENT_GATEWAY_TOKEN_PASSED,

            ProcessInterface::EVENT_PROCESS_INSTANCE_CREATED,
            ThrowEventInterface::EVENT_THROW_TOKEN_ARRIVES,
            GatewayInterface::EVENT_GATEWAY_ACTIVATED,
            GatewayInterface::EVENT_GATEWAY_TOKEN_CONSUMED,
            ThrowEventInterface::EVENT_THROW_TOKEN_CONSUMED,
            ThrowEventInterface::EVENT_THROW_TOKEN_PASSED,
            CatchEventInterface::EVENT_CATCH_TOKEN_ARRIVES,
            CatchEventInterface::EVENT_CATCH_TOKEN_ARRIVES,
            EventInterface::EVENT_EVENT_TRIGGERED,
            GatewayInterface::EVENT_GATEWAY_TOKEN_ARRIVES,
            GatewayInterface::EVENT_GATEWAY_ACTIVATED,
            GatewayInterface::EVENT_GATEWAY_TOKEN_CONSUMED,
            GatewayInterface::EVENT_GATEWAY_TOKEN_PASSED,
            GatewayInterface::EVENT_GATEWAY_TOKEN_PASSED,
            ActivityInterface::EVENT_ACTIVITY_ACTIVATED,
            ActivityInterface::EVENT_ACTIVITY_ACTIVATED,
        ]);

        // Assertion: We have two tokens in the main process (in the intermediate catch events)
        $this->assertEquals(2, $this->instance->getTokens()->count());
        $this->assertEquals(1, $catch1->getTokens($this->instance)->count());
        $this->assertEquals(1, $catch2->getTokens($this->instance)->count());

        // Complete the 'Approve' task
        $manager = $managerProcess->getInstances()->item(0);
        $this->completeTask($approve, $manager);
        $this->assertEvents([
            ActivityInterface::EVENT_ACTIVITY_COMPLETED,
            IntermediateCatchEventInterface::EVENT_CATCH_MESSAGE_CATCH,
            ThrowEventInterface::EVENT_THROW_TOKEN_ARRIVES,
            IntermediateCatchEventInterface::EVENT_CATCH_TOKEN_CONSUMED,
            IntermediateCatchEventInterface::EVENT_CATCH_MESSAGE_CONSUMED,
            IntermediateCatchEventInterface::EVENT_CATCH_TOKEN_PASSED,
            EventInterface::EVENT_EVENT_TRIGGERED,
            IntermediateCatchEventInterface::EVENT_CATCH_TOKEN_CONSUMED,
            ActivityInterface::EVENT_ACTIVITY_ACTIVATED,
            ThrowEventInterface::EVENT_THROW_TOKEN_CONSUMED,
            ActivityInterface::EVENT_ACTIVITY_CLOSED,
            ThrowEventInterface::EVENT_THROW_TOKEN_PASSED,
            ThrowEventInterface::EVENT_THROW_TOKEN_ARRIVES,
            ThrowEventInterface::EVENT_THROW_TOKEN_CONSUMED,
            EventInterface::EVENT_EVENT_TRIGGERED,
        ]);

        // Assertion: We have one token in the main process (and it is in the task 2)
        $this->assertEquals(1, $this->instance->getTokens()->count());
        $this->assertEquals(1, $task2->getTokens($this->instance)->count());
    }

    /**
     * Test the WCP16 Deferred Choice pattern
     *
     */
    public function testDeferredChoiceChoiceTwo()
    {
        // Load a BpmnFile Repository
        $bpmnRepository = new BpmnDocument();
        $bpmnRepository->setEngine($this->engine);
        $bpmnRepository->setFactory($this->repository);
        $bpmnRepository->load(__DIR__ . '/files/WCP16_DeferredChoiceEBG.bpmn');

        // Load the collaboration
        $bpmnRepository->getProcess('COLLABORATION_1');

        // Load a process from a bpmn repository by Id
        $process = $bpmnRepository->getProcess('PROCESS_1');
        $managerProcess = $bpmnRepository->getProcess('PROCESS_2');

        //Get start event and event definition references
        $startTask = $bpmnRepository->getScriptTask('_5');
        $reject = $bpmnRepository->getScriptTask('_19');
        $catch1 = $bpmnRepository->getIntermediateCatchEvent('_25');
        $catch2 = $bpmnRepository->getIntermediateCatchEvent('_27');

        $task2 = $bpmnRepository->getScriptTask('_29');
        $task3 = $bpmnRepository->getScriptTask('_31');
        //$timer = $bpmnRepository->getIntermediateCatchEvent('IntermediateCatchEvent_Timer');

        // Start the process
        $this->instance = $process->call();
        $this->engine->runToNextState();

        // Complete the 'start' activity
        $this->completeTask($startTask);

        // Assertion: Verify that Error End Event was triggered and the process is completed
        $this->assertEvents([
            ProcessInterface::EVENT_PROCESS_INSTANCE_CREATED,
            EventInterface::EVENT_EVENT_TRIGGERED,
            ActivityInterface::EVENT_ACTIVITY_ACTIVATED,
            ActivityInterface::EVENT_ACTIVITY_COMPLETED,
            GatewayInterface::EVENT_GATEWAY_TOKEN_ARRIVES,
            GatewayInterface::EVENT_GATEWAY_ACTIVATED,
            GatewayInterface::EVENT_GATEWAY_TOKEN_CONSUMED,
            ActivityInterface::EVENT_ACTIVITY_CLOSED,
            GatewayInterface::EVENT_GATEWAY_TOKEN_PASSED,
            GatewayInterface::EVENT_GATEWAY_TOKEN_ARRIVES,
            GatewayInterface::EVENT_GATEWAY_TOKEN_PASSED,

            ProcessInterface::EVENT_PROCESS_INSTANCE_CREATED,
            ThrowEventInterface::EVENT_THROW_TOKEN_ARRIVES,
            GatewayInterface::EVENT_GATEWAY_ACTIVATED,
            GatewayInterface::EVENT_GATEWAY_TOKEN_CONSUMED,
            ThrowEventInterface::EVENT_THROW_TOKEN_CONSUMED,
            ThrowEventInterface::EVENT_THROW_TOKEN_PASSED,
            CatchEventInterface::EVENT_CATCH_TOKEN_ARRIVES,
            CatchEventInterface::EVENT_CATCH_TOKEN_ARRIVES,
            EventInterface::EVENT_EVENT_TRIGGERED,
            GatewayInterface::EVENT_GATEWAY_TOKEN_ARRIVES,
            GatewayInterface::EVENT_GATEWAY_ACTIVATED,
            GatewayInterface::EVENT_GATEWAY_TOKEN_CONSUMED,
            GatewayInterface::EVENT_GATEWAY_TOKEN_PASSED,
            GatewayInterface::EVENT_GATEWAY_TOKEN_PASSED,
            ActivityInterface::EVENT_ACTIVITY_ACTIVATED,
            ActivityInterface::EVENT_ACTIVITY_ACTIVATED,
        ]);

        // Assertion: We have two tokens in the main process (in the intermediate catch events)
        $this->assertEquals(2, $this->instance->getTokens()->count());
        $this->assertEquals(1, $catch1->getTokens($this->instance)->count());
        $this->assertEquals(1, $catch2->getTokens($this->instance)->count());

        // Complete the 'Approve' task
        $manager = $managerProcess->getInstances()->item(0);
        $this->completeTask($reject, $manager);

        $this->assertEvents([
            ActivityInterface::EVENT_ACTIVITY_COMPLETED,
            IntermediateCatchEventInterface::EVENT_CATCH_MESSAGE_CATCH,
            ThrowEventInterface::EVENT_THROW_TOKEN_ARRIVES,
            IntermediateCatchEventInterface::EVENT_CATCH_TOKEN_CONSUMED,
            IntermediateCatchEventInterface::EVENT_CATCH_MESSAGE_CONSUMED,
            IntermediateCatchEventInterface::EVENT_CATCH_TOKEN_PASSED,
            EventInterface::EVENT_EVENT_TRIGGERED,
            IntermediateCatchEventInterface::EVENT_CATCH_TOKEN_CONSUMED,
            ActivityInterface::EVENT_ACTIVITY_ACTIVATED,
            ThrowEventInterface::EVENT_THROW_TOKEN_CONSUMED,
            ActivityInterface::EVENT_ACTIVITY_CLOSED,
            ThrowEventInterface::EVENT_THROW_TOKEN_PASSED,
            ThrowEventInterface::EVENT_THROW_TOKEN_ARRIVES,
            ThrowEventInterface::EVENT_THROW_TOKEN_CONSUMED,
            EventInterface::EVENT_EVENT_TRIGGERED,
        ]);

        // Assertion: We have one token in the main process (and it is in the task 3)
        $this->assertEquals(1, $this->instance->getTokens()->count());
        $this->assertEquals(1, $task3->getTokens($this->instance)->count());
    }

    /**
     * Complete an active task
     *
     * @param \ProcessMaker\Nayra\Contracts\Bpmn\ActivityInterface $task
     * @param \ProcessMaker\Nayra\Contracts\Engine\ExecutionInstanceInterface|null $instance
     *
     * @return void
     */
    private function completeTask(ActivityInterface $task, ExecutionInstanceInterface $instance = null)
    {
        $token = $task->getTokens($instance ? $instance : $this->instance)->item(0);
        $task->complete($token);
        $this->engine->runToNextState();
    }
}
