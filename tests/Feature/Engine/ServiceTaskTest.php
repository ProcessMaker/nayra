<?php

namespace Tests\Feature\Engine;

use Exception;
use ProcessMaker\Nayra\Contracts\Bpmn\ActivityInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\EndEventInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\EventInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\ProcessInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\ServiceTaskInterface;
use ProcessMaker\Nayra\Storage\BpmnDocument;

/**
 * Tests for the ServiceTask element
 */
class ServiceTaskTest extends EngineTestCase
{
    // Property that is increased when the service task is executed.
    private static $serviceCalls = 0;

    /**
     * Tests the a process with the sequence start->serviceTask->End executes correctly
     */
    public function testProcessWithServiceTask()
    {
        //Load a BpmnFile Repository
        $bpmnRepository = new BpmnDocument();
        $bpmnRepository->setEngine($this->engine);
        $bpmnRepository->setFactory($this->repository);
        $bpmnRepository->load(__DIR__.'/files/ServiceTaskProcess.bpmn');

        //Load a process from a bpmn repository by Id
        $process = $bpmnRepository->getProcess('ServiceTaskProcess');
        $dataStore = $this->repository->createDataStore();

        //create an instance of the process
        $instance = $this->engine->createExecutionInstance($process, $dataStore);

        //Get References
        $start = $process->getEvents()->item(0);
        $serviceTask = $bpmnRepository->getServiceTask('_2');
        static::$serviceCalls = 0;
        $serviceCalls = static::$serviceCalls;

        //start the process an instance of the process
        $start->start($instance);
        $this->engine->runToNextState();

        //Assert: that the process is stared and the service task is activated
        $this->assertEvents([
            ProcessInterface::EVENT_PROCESS_INSTANCE_CREATED,
            EventInterface::EVENT_EVENT_TRIGGERED,
            ActivityInterface::EVENT_ACTIVITY_ACTIVATED,
            ServiceTaskInterface::EVENT_SERVICE_TASK_ACTIVATED,
        ]);

        //Execute the service task
        $token = $serviceTask->getTokens($instance)->item(0);
        $serviceTask->run($token);
        $this->engine->runToNextState();

        //Assertion: The service task must be completed
        $this->assertEvents([
            ActivityInterface::EVENT_ACTIVITY_COMPLETED,
            ActivityInterface::EVENT_ACTIVITY_CLOSED,
            EndEventInterface::EVENT_THROW_TOKEN_ARRIVES,
            EndEventInterface::EVENT_THROW_TOKEN_CONSUMED,
            EndEventInterface::EVENT_EVENT_TRIGGERED,
            ProcessInterface::EVENT_PROCESS_INSTANCE_COMPLETED,
        ]);

        //Assertion: Verify the method convertToDoc was called
        $this->assertEquals($serviceCalls + 1, static::$serviceCalls);
    }

    /**
     * Tests a process with the sequence start->serviceTask->End when fails
     * the task goes to a failure state.
     */
    public function testProcessWithServiceTaskFailure()
    {
        //Load a BpmnFile Repository
        $bpmnRepository = new BpmnDocument();
        $bpmnRepository->setEngine($this->engine);
        $bpmnRepository->setFactory($this->repository);
        $bpmnRepository->load(__DIR__.'/files/ServiceTaskProcess.bpmn');

        //Load a process from a bpmn repository by Id
        $process = $bpmnRepository->getProcess('ServiceTaskProcess');
        $dataStore = $this->repository->createDataStore();

        //create an instance of the process
        $instance = $this->engine->createExecutionInstance($process, $dataStore);

        //Get References
        $start = $process->getEvents()->item(0);
        $serviceTask = $bpmnRepository->getServiceTask('_2');

        //Set to the limit of the service calls
        static::$serviceCalls = 10;
        $serviceCalls = static::$serviceCalls;

        //start the process an instance of the process
        $start->start($instance);
        $this->engine->runToNextState();

        //Assert: that the process is stared and the service task is activated
        $this->assertEvents([
            ProcessInterface::EVENT_PROCESS_INSTANCE_CREATED,
            EventInterface::EVENT_EVENT_TRIGGERED,
            ActivityInterface::EVENT_ACTIVITY_ACTIVATED,
            ServiceTaskInterface::EVENT_SERVICE_TASK_ACTIVATED,
        ]);

        //Execute the service task
        $token = $serviceTask->getTokens($instance)->item(0);
        $serviceTask->run($token);
        $this->engine->runToNextState();

        //Assertion: The service task must go to exception state
        $this->assertEvents([
            ActivityInterface::EVENT_ACTIVITY_EXCEPTION,
        ]);
    }

    /**
     * A public function that is called by the Service Task.
     */
    public static function convertToDoc()
    {
        if (static::$serviceCalls === 10) {
            throw new Exception('Limit of executions reached.');
        }
        static::$serviceCalls++;
    }
}
