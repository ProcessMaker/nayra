<?php

namespace Tests\Feature\Engine;

use ProcessMaker\Models\ActivityActivatedEvent;
use ProcessMaker\Nayra\Contracts\Bpmn\ActivityInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\GatewayInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\ProcessInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\StartEventInterface;
use ProcessMaker\Repositories\BpmnFileRepository;

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
                            function($payload) {
            $this->storage[$payload[1]->getId()] = [
                'processId' => $payload[0]->getId(),
                'data'      => [],
                'tokens'    => [],
                'status'    => 'ACTIVE',
            ];
        });
        $dispatcher->listen(ProcessInterface::EVENT_PROCESS_INSTANCE_COMPLETED,
                            function($payload) {
            $this->storage[$payload[1]->getId()]['status'] = 'COMPLETED';
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
                            function($payload) {
            $id = $payload[1]->getInstance()->getId();
            $this->storage[$id]['tokens'][$payload[1]->getId()] = [
                'elementId' => $payload[0]->getId(),
                'status'    => $payload[1]->getStatus(),
            ];
        });
        $dispatcher->listen(ActivityInterface::EVENT_ACTIVITY_CLOSED,
                            function($payload) {
            $id = $payload[1]->getInstance()->getId();
            $this->storage[$id]['tokens'][$payload[1]->getId()] = [
                'elementId' => $payload[0]->getId(),
                'status'    => $payload[1]->getStatus(),
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
        $bpmnRepository = new BpmnFileRepository();
        $bpmnRepository->load(__DIR__ . '/files/LoadTokens.bpmn');
        $bpmnRepository->setEngine($this->engine);
        $this->engine->setRepositoryFactory($bpmnRepository);

        //Call the process
        $process = $bpmnRepository->loadBpmElementById('SequentialTask');
        $instance = $process->call();
        $this->engine->runToNextState();

        //Completes the first activity
        $firstActivity = $bpmnRepository->loadBpmElementById('first');
        $token = $firstActivity->getTokens($instance)->item(0);
        $firstActivity->complete($token);
        $this->engine->runToNextState();

        //Assertion: Verify that first activity was activated, completed and closed, then the second is activated
        $this->assertEvents([
            ProcessInterface::EVENT_PROCESS_INSTANCE_CREATED,
            StartEventInterface::EVENT_EVENT_TRIGGERED,
            ActivityInterface::EVENT_ACTIVITY_ACTIVATED,
            ActivityInterface::EVENT_ACTIVITY_COMPLETED,
            ActivityInterface::EVENT_ACTIVITY_CLOSED,
            ActivityInterface::EVENT_ACTIVITY_ACTIVATED,
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
        $bpmnRepository = new BpmnFileRepository();
        $bpmnRepository->load(__DIR__ . '/files/LoadTokens.bpmn');
        $bpmnRepository->setEngine($this->engine);
        $this->engine->setRepositoryFactory($bpmnRepository);

        //Call the process
        $process = $bpmnRepository->loadBpmElementById('ParallelProcess');
        $instance = $process->call();
        $this->engine->runToNextState();

        //Completes the first task
        $firstTask = $bpmnRepository->loadBpmElementById('task1');
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
        $secondtTask = $bpmnRepository->loadBpmElementById('task2');
        $token = $secondtTask->getTokens($instance)->item(0);
        $secondtTask->complete($token);
        $this->engine->runToNextState();

        //Assertion: Saved data show the first activity was closed, the second is completed, and the third is active
        $this->assertSavedTokens($instance->getId(), [
            ['elementId' => 'task1', 'status' => ActivityInterface::TOKEN_STATE_CLOSED],
            ['elementId' => 'task2', 'status' => ActivityInterface::TOKEN_STATE_COMPLETED],
            ['elementId' => 'task3', 'status' => ActivityInterface::TOKEN_STATE_ACTIVE],
        ]);

        //Completes the third task
        $thirdTask = $bpmnRepository->loadBpmElementById('task3');
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
