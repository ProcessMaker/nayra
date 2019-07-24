<?php

namespace Tests\Feature\Engine;

use ProcessMaker\Nayra\Contracts\Bpmn\ActivityInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\EndEventInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\EventInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\ProcessInterface;
use ProcessMaker\Nayra\Storage\BpmnDocument;
use ProcessMaker\Nayra\Contracts\Bpmn\GatewayInterface;
use ProcessMaker\Nayra\Bpmn\Models\SignalEventDefinition;
use ProcessMaker\Nayra\Contracts\Bpmn\BoundaryEventInterface;

/**
 * Tests for the BoundaryEvent element
 *
 */
class BoundaryEventTest extends EngineTestCase
{
    /**
     * Tests the a process with a signal boundary event
     */
    public function testSignalBoundaryEvent()
    {
        // Load a BpmnFile Repository
        $bpmnRepository = new BpmnDocument();
        $bpmnRepository->setEngine($this->engine);
        $bpmnRepository->setFactory($this->repository);
        $bpmnRepository->load(__DIR__ . '/files/Signal_BoundaryEvent.bpmn');

        // Load a process from a bpmn repository by Id
        $process = $bpmnRepository->getProcess('PROCESS_1');
        $dataStore = $this->repository->createDataStore();

        // create an instance of the process
        $instance = $this->engine->createExecutionInstance($process, $dataStore);

        // Get References
        $start = $process->getEvents()->item(0);
        $task1 = $bpmnRepository->getActivity('_5');
        $task2 = $bpmnRepository->getActivity('_7');
        $task3 = $bpmnRepository->getActivity('_12');

        // Start a process instance
        $start->start($instance);
        $this->engine->runToNextState();

        // Assert: that the process is stared and two tasks were activated
        $this->assertEvents([
            ProcessInterface::EVENT_PROCESS_INSTANCE_CREATED,
            EventInterface::EVENT_EVENT_TRIGGERED,
            GatewayInterface::EVENT_GATEWAY_TOKEN_ARRIVES,
            GatewayInterface::EVENT_GATEWAY_ACTIVATED,
            GatewayInterface::EVENT_GATEWAY_TOKEN_CONSUMED,
            GatewayInterface::EVENT_GATEWAY_TOKEN_PASSED,
            GatewayInterface::EVENT_GATEWAY_TOKEN_PASSED,
            ActivityInterface::EVENT_ACTIVITY_ACTIVATED,
            ActivityInterface::EVENT_ACTIVITY_ACTIVATED,
        ]);

        // Complete second task
        $task2->complete($task2->getTokens($instance)->item(0));
        $this->engine->runToNextState();

        // Assert: Task 2 is completed, an end event Signal is thrown, then caught by the boundary event
        $this->assertEvents([
            ActivityInterface::EVENT_ACTIVITY_COMPLETED,
            ActivityInterface::EVENT_ACTIVITY_CLOSED,
            EndEventInterface::EVENT_THROW_TOKEN_ARRIVES,
            SignalEventDefinition::EVENT_THROW_EVENT_DEFINITION,
            BoundaryEventInterface::EVENT_BOUNDARY_EVENT_CATCH,
            BoundaryEventInterface::EVENT_BOUNDARY_EVENT_CONSUMED,
            ActivityInterface::EVENT_ACTIVITY_CANCELLED, //Activity Cancelled
            ActivityInterface::EVENT_ACTIVITY_ACTIVATED,
            EndEventInterface::EVENT_THROW_TOKEN_CONSUMED,
            EndEventInterface::EVENT_EVENT_TRIGGERED,
        ]);

        // Assert: Task 1 does not have tokens
        $this->assertEquals(0, $task1->getTokens($instance)->count());

        // Assert: Task 3 has one token
        $this->assertEquals(1, $task3->getTokens($instance)->count());
    }
}
