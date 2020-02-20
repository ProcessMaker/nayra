<?php

namespace Tests\Feature\Engine;

use Exception;
use ProcessMaker\Nayra\Bpmn\Events\ActivityActivatedEvent;
use ProcessMaker\Nayra\Bpmn\Events\ActivityClosedEvent;
use ProcessMaker\Nayra\Bpmn\Events\ActivityCompletedEvent;
use ProcessMaker\Nayra\Bpmn\Events\ProcessInstanceCompletedEvent;
use ProcessMaker\Nayra\Bpmn\Events\ProcessInstanceCreatedEvent;
use ProcessMaker\Nayra\Contracts\Bpmn\ActivityInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\GatewayInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\ProcessInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\ScriptTaskInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\StartEventInterface;
use ProcessMaker\Nayra\Storage\BpmnDocument;
use ProcessMaker\Test\Models\TokenRepository;

/**
 * Test to save execution instances
 *
 */
class SaveExecutionInstancesTest extends EngineTestCase
{
    /**
     * Array where to save the tokens of the execution instance tested.
     *
     * @var array $storage
     */
    private $storage = [];

    /**
     * Configure the Listener to save the tokens and instances.
     *
     */
    protected function setUp()
    {
        parent::setUp();
        //Prepare the listener to save tokens
        $dispatcher = $this->engine->getDispatcher();
        $dispatcher->listen(ProcessInterface::EVENT_PROCESS_INSTANCE_CREATED,
                            function(ProcessInstanceCreatedEvent $payload) {
            $this->storage[$payload->instance->getId()] = [
                'processId' => $payload->process->getId(),
                'data'      => [],
                'tokens'    => [],
                'status'    => 'ACTIVE',
            ];
        });
        $dispatcher->listen(ProcessInterface::EVENT_PROCESS_INSTANCE_COMPLETED,
                            function(ProcessInstanceCompletedEvent $payload) {
            $this->storage[$payload->instance->getId()]['status'] = 'COMPLETED';
        });
        $dispatcher->listen(ActivityInterface::EVENT_ACTIVITY_ACTIVATED,
                            function(ActivityActivatedEvent $event) {
            $id = $event->token->getInstance()->getId();
            $this->storage[$id]['tokens'][$event->token->getId()] = [
                'elementId' => $event->activity->getId(),
                'status'    => $event->token->getStatus(),
            ];
        });
        $dispatcher->listen(ActivityInterface::EVENT_ACTIVITY_COMPLETED,
                            function(ActivityCompletedEvent $event) {
            $id = $event->token->getInstance()->getId();
            $this->storage[$id]['tokens'][$event->token->getId()] = [
                'elementId' => $event->activity->getId(),
                'status'    => $event->token->getStatus(),
            ];
        });
        $dispatcher->listen(ActivityInterface::EVENT_ACTIVITY_CLOSED,
                            function(ActivityClosedEvent $event) {
            $id = $event->token->getInstance()->getId();
            $this->storage[$id]['tokens'][$event->token->getId()] = [
                'elementId' => $event->activity->getId(),
                'status'    => $event->token->getStatus(),
            ];
        });
        //Prepare a clean storage.
        $this->storage = [];
    }

    /**
     * Test to save a sequential process with one active token.
     *
     */
    public function testSaveExecutionInstanceWithOneToken()
    {
        //Load a BpmnFile Repository
        $bpmnRepository = new BpmnDocument();
        $bpmnRepository->setEngine($this->engine);
        $bpmnRepository->setFactory($this->repository);
        $bpmnRepository->load(__DIR__ . '/files/LoadTokens.bpmn');

        //Call the process
        $process = $bpmnRepository->getProcess('SequentialTask');
        $instance = $process->call();
        $this->engine->runToNextState();

        //Completes the first activity
        $firstActivity = $bpmnRepository->getActivity('first');
        $token = $firstActivity->getTokens($instance)->item(0);
        $firstActivity->complete($token);
        $this->engine->runToNextState();

        //Assertion: Verify that first activity was activated, completed and closed, then the second is activated
        $this->assertEvents([
            ProcessInterface::EVENT_PROCESS_INSTANCE_CREATED,
            StartEventInterface::EVENT_EVENT_TRIGGERED,
            ActivityInterface::EVENT_ACTIVITY_ACTIVATED,
            ScriptTaskInterface::EVENT_SCRIPT_TASK_ACTIVATED,
            ActivityInterface::EVENT_ACTIVITY_COMPLETED,
            ActivityInterface::EVENT_ACTIVITY_CLOSED,
            ActivityInterface::EVENT_ACTIVITY_ACTIVATED,
            ScriptTaskInterface::EVENT_SCRIPT_TASK_ACTIVATED,
        ]);

        //Assertion: Saved data show the first activity was closed, the second is active
        $this->assertSavedTokens($instance->getId(), [
            ['elementId' => 'first', 'status' => ActivityInterface::TOKEN_STATE_CLOSED],
            ['elementId' => 'second', 'status' => ActivityInterface::TOKEN_STATE_ACTIVE],
        ]);
    }

    /**
     * Test save execution instance with multiple tokens
     *
     */
    public function testSaveExecutionInstanceWithMultipleTokens()
    {
        //Load a BpmnFile Repository
        $bpmnRepository = new BpmnDocument();
        $bpmnRepository->setEngine($this->engine);
        $bpmnRepository->setFactory($this->repository);
        $bpmnRepository->load(__DIR__ . '/files/LoadTokens.bpmn');

        //Call the process
        $process = $bpmnRepository->getProcess('ParallelProcess');
        $instance = $process->call();
        $this->engine->runToNextState();

        //Completes the first task
        $firstTask = $bpmnRepository->getActivity('task1');
        $token = $firstTask->getTokens($instance)->item(0);
        $firstTask->complete($token);
        $this->engine->runToNextState();

        //Assertion: Verify that gateway was activated, the two tasks activate, one completed and another activated
        $this->assertEvents([
            ProcessInterface::EVENT_PROCESS_INSTANCE_CREATED,
            StartEventInterface::EVENT_EVENT_TRIGGERED,
            GatewayInterface::EVENT_GATEWAY_TOKEN_ARRIVES,
            GatewayInterface::EVENT_GATEWAY_ACTIVATED,
            GatewayInterface::EVENT_GATEWAY_TOKEN_CONSUMED,
            GatewayInterface::EVENT_GATEWAY_TOKEN_PASSED,
            GatewayInterface::EVENT_GATEWAY_TOKEN_PASSED,
            ActivityInterface::EVENT_ACTIVITY_ACTIVATED,
            ActivityInterface::EVENT_ACTIVITY_ACTIVATED,
            ActivityInterface::EVENT_ACTIVITY_COMPLETED,
            ActivityInterface::EVENT_ACTIVITY_CLOSED,
            ActivityInterface::EVENT_ACTIVITY_ACTIVATED,
        ]);

        //Assertion: Saved data show the first activity was closed, the second is active, and the third is active
        $this->assertSavedTokens($instance->getId(), [
            ['elementId' => 'task1', 'status' => ActivityInterface::TOKEN_STATE_CLOSED],
            ['elementId' => 'task2', 'status' => ActivityInterface::TOKEN_STATE_ACTIVE],
            ['elementId' => 'task3', 'status' => ActivityInterface::TOKEN_STATE_ACTIVE],
        ]);

        //Completes the second task
        $secondtTask = $bpmnRepository->getActivity('task2');
        $token = $secondtTask->getTokens($instance)->item(0);
        $secondtTask->complete($token);
        $this->engine->runToNextState();

        //Assertion: Saved data show the first activity was closed, the second is completed, and the third is active
        $this->assertSavedTokens($instance->getId(), [
            ['elementId' => 'task1', 'status' => ActivityInterface::TOKEN_STATE_CLOSED],
            ['elementId' => 'task2', 'status' => ActivityInterface::TOKEN_STATE_CLOSED],
            ['elementId' => 'task3', 'status' => ActivityInterface::TOKEN_STATE_ACTIVE],
        ]);

        //Completes the third task
        $thirdTask = $bpmnRepository->getActivity('task3');
        $token = $thirdTask->getTokens($instance)->item(0);
        $thirdTask->complete($token);
        $this->engine->runToNextState();

        //Assertion: Saved data show the first activity was closed, the second is closed, and the third is closed
        $this->assertSavedTokens($instance->getId(), [
            ['elementId' => 'task1', 'status' => ActivityInterface::TOKEN_STATE_CLOSED],
            ['elementId' => 'task2', 'status' => ActivityInterface::TOKEN_STATE_CLOSED],
            ['elementId' => 'task3', 'status' => ActivityInterface::TOKEN_STATE_CLOSED],
        ]);
    }

    /**
     * Test save execution with an exception.
     *
     * @return void
     */
    public function testFailSaveToken()
    {
        //Load a BpmnFile Repository
        $bpmnRepository = new BpmnDocument();
        $bpmnRepository->setEngine($this->engine);
        $bpmnRepository->setFactory($this->repository);
        $bpmnRepository->load(__DIR__ . '/files/LoadTokens.bpmn');

        //Call the process
        $process = $bpmnRepository->getProcess('ParallelProcess');
        $instance = $process->call();
        $this->engine->runToNextState();

        //Completes the first task
        $firstTask = $bpmnRepository->getActivity('task1');
        $token = $firstTask->getTokens($instance)->item(0);

        //Assertion: Expected Exception when save token
        $this->expectException(Exception::class);
        TokenRepository::failNextPersistanceCall();
        $firstTask->complete($token);
        $this->engine->runToNextState();
    }

    /**
     * Verify if the saved data is the expected.
     *
     * @param string $instanceId
     * @param array $expected
     * @param string $message
     */
    private function assertSavedTokens($instanceId, array $expected, $message = 'Saved data is not the expected')
    {
        $tokens = array_values($this->storage[$instanceId]['tokens']);
        //Assertion: Verify the saved data
        $this->assertEquals($expected, $tokens, $message);
    }
}
