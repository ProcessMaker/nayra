<?php

namespace Tests\Feature\Engine;

use ProcessMaker\Nayra\Contracts\Bpmn\ActivityInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\EndEventInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\EventInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\GatewayInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\ProcessInterface;
use ProcessMaker\Repositories\BpmnFileRepository;

/**
 * Test load and save execution instances
 *
 */
class LoadExecutionInstancesTest extends EngineTestCase
{

    /**
     * Set data for a sequential process
     *
     */
    private function prepareSequentialProcess()
    {
        $executionInstanceRepository = $this->engine->getRepositoryFactory()->getExecutionInstanceRepository();
        $executionInstanceRepository->setRawData([
            'executionInstanceId' => [
                'processId' => 'SequentialTask',
                'data' => [],
                'tokens' => [
                    [
                        'elementId' => 'second',
                        'status' => ActivityInterface::TOKEN_STATE_ACTIVE,
                    ]
                ],
            ]
        ]);
    }

    /**
     * Set data for a parallel process
     *
     */
    private function prepareParallelProcess()
    {
        $executionInstanceRepository = $this->engine->getRepositoryFactory()->getExecutionInstanceRepository();
        $executionInstanceRepository->setRawData([
            'otherExecutionInstanceId' => [
                'processId' => 'ParallelProcess',
                'data' => [],
                'tokens' => [
                    [
                        'elementId' => 'task2',
                        'status' => ActivityInterface::TOKEN_STATE_ACTIVE,
                    ],
                    [
                        'elementId' => 'task3',
                        'status' => ActivityInterface::TOKEN_STATE_ACTIVE,
                    ],
                ],
            ]
        ]);
    }

    /**
     * Set data for a parallel process with an activity just completed
     *
     */
    private function prepareParallelProcessWithActivityCompleted()
    {
        $executionInstanceRepository = $this->engine->getRepositoryFactory()->getExecutionInstanceRepository();
        $executionInstanceRepository->setRawData([
            'otherExecutionInstanceId' => [
                'processId' => 'ParallelProcess',
                'data' => [],
                'tokens' => [
                    [
                        'elementId' => 'task2',
                        'status' => ActivityInterface::TOKEN_STATE_COMPLETED,
                    ],
                    [
                        'elementId' => 'task3',
                        'status' => ActivityInterface::TOKEN_STATE_ACTIVE,
                    ],
                ],
            ]
        ]);
    }

    /**
     * Set data for a parallel process with an activity in exception state
     *
     */
    private function prepareParallelProcessWithException()
    {
        $executionInstanceRepository = $this->engine->getRepositoryFactory()->getExecutionInstanceRepository();
        $executionInstanceRepository->setRawData([
            'otherExecutionInstanceId' => [
                'processId' => 'ParallelProcess',
                'data' => [],
                'tokens' => [
                    [
                        'elementId' => 'task2',
                        'status' => ActivityInterface::TOKEN_STATE_ACTIVE,
                    ],
                    [
                        'elementId' => 'task3',
                        'status' => ActivityInterface::TOKEN_STATE_FAILING,
                    ],
                ],
            ]
        ]);
    }

    /**
     * Test load an execution instance from repository with one token
     *
     */
    public function testLoadExecutionInstanceWithOneToken()
    {
        //Load a BpmnFile Repository
        $bpmnRepository = new BpmnFileRepository();
        $bpmnRepository->load(__DIR__ . '/files/LoadTokens.bpmn');
        $bpmnRepository->setEngine($this->engine);
        $this->engine->setRepositoryFactory($bpmnRepository);

        //Set test data to load the sequential process
        $this->prepareSequentialProcess();

        //Load the execution instance
        $instance = $this->engine->loadExecutionInstance('executionInstanceId');

        //Get References by id
        $secondActivity = $bpmnRepository->loadBpmElementById('second');

        //Completes the second activity
        $token = $secondActivity->getTokens($instance)->item(0);
        $secondActivity->complete($token);
        $this->engine->runToNextState();

        //Assertion: Second activity was completed and closed, then the process has ended.
        $this->assertEvents([
            ActivityInterface::EVENT_ACTIVITY_COMPLETED,
            ActivityInterface::EVENT_ACTIVITY_CLOSED,
            EndEventInterface::EVENT_THROW_TOKEN_ARRIVES,
            EndEventInterface::EVENT_THROW_TOKEN_CONSUMED,
            EventInterface::EVENT_EVENT_TRIGGERED,
            ProcessInterface::EVENT_PROCESS_COMPLETED,
        ]);
    }

    /**
     * Test load an execution instance from repository with multiple tokens
     *
     */
    public function testLoadExecutionInstanceWithMultipleTokens()
    {
        //Load a BpmnFile Repository
        $bpmnRepository = new BpmnFileRepository();
        $bpmnRepository->load(__DIR__ . '/files/LoadTokens.bpmn');
        $bpmnRepository->setEngine($this->engine);
        $this->engine->setRepositoryFactory($bpmnRepository);

        //Set test data to load the sequential process
        $this->prepareParallelProcess();

        //Load the execution instance
        $instance = $this->engine->loadExecutionInstance('otherExecutionInstanceId');

        //Get References by id
        $secondActivity = $bpmnRepository->loadBpmElementById('task2');
        $thirdActivity = $bpmnRepository->loadBpmElementById('task3');

        //Completes the second activity
        $token = $secondActivity->getTokens($instance)->item(0);
        $secondActivity->complete($token);
        $this->engine->runToNextState();

        //Completes the third activity
        $token = $thirdActivity->getTokens($instance)->item(0);
        $thirdActivity->complete($token);
        $this->engine->runToNextState();

        //Assertion: Second and third activity are completed and closed, the gateway is activiate, and the process ends.
        $this->assertEvents([
            ActivityInterface::EVENT_ACTIVITY_COMPLETED,
            ActivityInterface::EVENT_ACTIVITY_CLOSED,
            GatewayInterface::EVENT_GATEWAY_TOKEN_ARRIVES,
            ActivityInterface::EVENT_ACTIVITY_COMPLETED,
            ActivityInterface::EVENT_ACTIVITY_CLOSED,
            GatewayInterface::EVENT_GATEWAY_TOKEN_ARRIVES,
            GatewayInterface::EVENT_GATEWAY_ACTIVATED,
            GatewayInterface::EVENT_GATEWAY_TOKEN_CONSUMED,
            GatewayInterface::EVENT_GATEWAY_TOKEN_CONSUMED,
            GatewayInterface::EVENT_GATEWAY_TOKEN_PASSED,
            EndEventInterface::EVENT_THROW_TOKEN_ARRIVES,
            EndEventInterface::EVENT_THROW_TOKEN_CONSUMED,
            EventInterface::EVENT_EVENT_TRIGGERED,
            ProcessInterface::EVENT_PROCESS_COMPLETED,
        ]);
    }

    /**
     * Test load an execution instance from repository with multiple tokens
     * in different states
     *
     */
    public function testLoadExecutionInstanceWithMultipleTokensStates()
    {
        //Load a BpmnFile Repository
        $bpmnRepository = new BpmnFileRepository();
        $bpmnRepository->load(__DIR__ . '/files/LoadTokens.bpmn');
        $bpmnRepository->setEngine($this->engine);
        $this->engine->setRepositoryFactory($bpmnRepository);

        //Set test data to load the sequential process
        $this->prepareParallelProcessWithActivityCompleted();

        //Load the execution instance
        $instance = $this->engine->loadExecutionInstance('otherExecutionInstanceId');

        //Get References by id
        $secondActivity = $bpmnRepository->loadBpmElementById('task2');
        $thirdActivity = $bpmnRepository->loadBpmElementById('task3');

        //Completes the third activity
        $token = $thirdActivity->getTokens($instance)->item(0);
        $thirdActivity->complete($token);
        $this->engine->runToNextState();

        //Assertion: Second activity is closed and the third activity are completed, then closed, the join gateway is activiate, and finally the process is completed.
        $this->assertEvents([
            ActivityInterface::EVENT_ACTIVITY_CLOSED,
            GatewayInterface::EVENT_GATEWAY_TOKEN_ARRIVES,
            ActivityInterface::EVENT_ACTIVITY_COMPLETED,
            ActivityInterface::EVENT_ACTIVITY_CLOSED,
            GatewayInterface::EVENT_GATEWAY_TOKEN_ARRIVES,
            GatewayInterface::EVENT_GATEWAY_ACTIVATED,
            GatewayInterface::EVENT_GATEWAY_TOKEN_CONSUMED,
            GatewayInterface::EVENT_GATEWAY_TOKEN_CONSUMED,
            GatewayInterface::EVENT_GATEWAY_TOKEN_PASSED,
            EndEventInterface::EVENT_THROW_TOKEN_ARRIVES,
            EndEventInterface::EVENT_THROW_TOKEN_CONSUMED,
            EventInterface::EVENT_EVENT_TRIGGERED,
            ProcessInterface::EVENT_PROCESS_COMPLETED,
        ]);
    }

    /**
     * Test load an execution instance from repository with multiple tokens
     * and one token in falling state
     *
     */
    public function testLoadExecutionInstanceWithMultipleTokensFallingState()
    {
        //Load a BpmnFile Repository
        $bpmnRepository = new BpmnFileRepository();
        $bpmnRepository->load(__DIR__ . '/files/LoadTokens.bpmn');
        $bpmnRepository->setEngine($this->engine);
        $this->engine->setRepositoryFactory($bpmnRepository);

        //Set test data to load the sequential process
        $this->prepareParallelProcessWithException();

        //Load the execution instance
        $instance = $this->engine->loadExecutionInstance('otherExecutionInstanceId');

        //Get References by id
        $thirdActivity = $bpmnRepository->loadBpmElementById('task3');

        //Assertion: The third activity is in falling state
        $token = $thirdActivity->getTokens($instance)->item(0);
        $this->assertEquals(ActivityInterface::TOKEN_STATE_FAILING, $token->getStatus());
    }
}
