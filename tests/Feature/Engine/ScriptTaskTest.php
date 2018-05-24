<?php

namespace Tests\Feature\Engine;

use ProcessMaker\Nayra\Contracts\Bpmn\ActivityInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\EndEventInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\EventInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\ProcessInterface;

/**
 * Tests for the ScriptTask element
 *
 */
class ScriptTaskTest extends EngineTestCase
{

    const TEST_PROPERTY = 'scriptTestTaskProp';

    /**
     * Tests the a process with the sequence start->Task1->scriptTask1->End executes correctly
     */
    public function testProcessWithOneScriptTask()
    {
        //Load a process
        $process = $this->getProcessWithOneScriptTask();
        $dataStore = $this->dataStoreRepository->createDataStoreInstance();

        //create an instance of the process
        $instance = $this->engine->createExecutionInstance($process, $dataStore);

        //Get References
        $start = $process->getEvents()->item(0);
        $activity1 = $process->getActivities()->item(0);
        $scriptTask = $process->getActivities()->item(1);

        //start the process an instance of the process
        $start->start();
        $this->engine->runToNextState();

        //Assert: that the process is stared and the first activity activated
        $this->assertEvents([
            EventInterface::EVENT_EVENT_TRIGGERED,
            ActivityInterface::EVENT_ACTIVITY_ACTIVATED,
        ]);

        //complete the first activity (that is a manual one)
        $token = $activity1->getTokens($instance)->item(0);
        $activity1->complete($token);
        $this->engine->runToNextState();

        //Assertion: The activity1 must be finished and the script task run immediately
        $this->assertEvents([
            ActivityInterface::EVENT_ACTIVITY_COMPLETED,
            ActivityInterface::EVENT_ACTIVITY_CLOSED,
            ActivityInterface::EVENT_ACTIVITY_ACTIVATED,
            ActivityInterface::EVENT_ACTIVITY_COMPLETED,
            ActivityInterface::EVENT_ACTIVITY_CLOSED,
            EndEventInterface::EVENT_THROW_TOKEN_ARRIVES,
            EndEventInterface::EVENT_THROW_TOKEN_CONSUMED,
            EndEventInterface::EVENT_EVENT_TRIGGERED,
            ProcessInterface::EVENT_PROCESS_COMPLETED,
        ]);

        //$this->assertScriptTaskExecuted($scriptTask);
        $this->assertEquals($scriptTask->getProperty(self::TEST_PROPERTY), 1);
    }

    /**
     * Tests that a process with the sequence start->ScriptTask1->scriptTask2->End runs correctly
     */
    public function testProcessWithScriptTasksOnly()
    {
        //Load a process
        $process = $this->getProcessWithOnlyScriptTasks();
        $dataStore = $this->dataStoreRepository->createDataStoreInstance();

        //create an instance of the process
        $instance = $this->engine->createExecutionInstance($process, $dataStore);

        //Get References
        $start = $process->getEvents()->item(0);
        $scriptTask1 = $process->getActivities()->item(0);
        $scriptTask2 = $process->getActivities()->item(1);

        //start the process an instance of the process
        $start->start();
        $this->engine->runToNextState();

        //Assertion: all activities should run and the process finish immediately
        $this->assertEvents([
            EventInterface::EVENT_EVENT_TRIGGERED,
            ActivityInterface::EVENT_ACTIVITY_ACTIVATED,
            ActivityInterface::EVENT_ACTIVITY_COMPLETED,
            ActivityInterface::EVENT_ACTIVITY_CLOSED,
            ActivityInterface::EVENT_ACTIVITY_ACTIVATED,
            ActivityInterface::EVENT_ACTIVITY_COMPLETED,
            ActivityInterface::EVENT_ACTIVITY_CLOSED,
            EndEventInterface::EVENT_THROW_TOKEN_ARRIVES,
            EndEventInterface::EVENT_THROW_TOKEN_CONSUMED,
            EndEventInterface::EVENT_EVENT_TRIGGERED,
            ProcessInterface::EVENT_PROCESS_COMPLETED,
        ]);

        $this->assertEquals($scriptTask1->getProperty(self::TEST_PROPERTY), 1);
        $this->assertEquals($scriptTask2->getProperty(self::TEST_PROPERTY), 1);
    }

    /**
     * Tests that when a script fails, the ScriptTask token is set to failed status
     */
    public function testScriptTaskThatFails()
    {
        //Load a process
        $process = $this->getProcessWithOneScriptTask();
        $dataStore = $this->dataStoreRepository->createDataStoreInstance();

        //create an instance of the process
        $instance = $this->engine->createExecutionInstance($process, $dataStore);

        //Get References
        $start = $process->getEvents()->item(0);
        $activity1 = $process->getActivities()->item(0);
        $scriptTask = $process->getActivities()->item(1);

        //set an script that evaluates with an error
        $scriptTask->setScript('throw new Exception ("test exception");');

        //start the process an instance of the process
        $start->start();
        $this->engine->runToNextState();

        //Assert: that the process is stared and the first activity activated
        $this->assertEvents([
            EventInterface::EVENT_EVENT_TRIGGERED,
            ActivityInterface::EVENT_ACTIVITY_ACTIVATED,
        ]);

        //complete the first activity (that is a manual one)
        $token = $activity1->getTokens($instance)->item(0);
        $activity1->complete($token);
        $this->engine->runToNextState();

        $scriptToken = $scriptTask->getTokens($instance)->item(0);
        $this->assertEquals($scriptToken->getStatus(), ActivityInterface::TOKEN_STATE_FAILING);
    }

    /**
     * Generates a process with the following elements: start->Task1->scriptTask1->End
     * @return \ProcessMaker\Models\Process
     */
    private function getProcessWithOneScriptTask()
    {
        $process = $this->processRepository->createProcessInstance();
        $process->setEngine($this->engine);

        //elements
        $start = $this->eventRepository->createStartEventInstance();
        $activityA = $this->activityRepository->createActivityInstance();
        $scriptTask = $this->activityRepository->createScriptTaskInstance();
        $scriptTask->setScriptFormat('text/php');
        $scriptTask->setScript('$this->setProperty("' . self::TEST_PROPERTY . '", 1);');

        $end = $this->eventRepository->createEndEventInstance();
        $process
            ->addActivity($activityA)
            ->addActivity($scriptTask);
        $process
            ->addEvent($start)
            ->addEvent($end);

        //flows
        $start->createFlowTo($activityA, $this->flowRepository);
        $activityA->createFlowTo($scriptTask, $this->flowRepository);
        $scriptTask->createFlowTo($end, $this->flowRepository);

        return $process;
    }

    /**
     * Generates a process with the following elements: start->scriptTask1->scriptTask2->End
     * @return \ProcessMaker\Models\Process
     */
    private function getProcessWithOnlyScriptTasks()
    {
        $process = $this->processRepository->createProcessInstance();
        $process->setEngine($this->engine);

        //elements
        $start = $this->eventRepository->createStartEventInstance();
        $scriptTask1 = $this->activityRepository->createScriptTaskInstance();
        $scriptTask2 = $this->activityRepository->createScriptTaskInstance();

        $scriptTask1->setScriptFormat('text/php');
        $scriptTask1->setScript('$this->setProperty("scriptTestTaskProp", 1);');

        $scriptTask2->setScriptFormat('text/php');
        $scriptTask2->setScript('$this->setProperty("scriptTestTaskProp", 1);');

        $end = $this->eventRepository->createEndEventInstance();
        $process
            ->addActivity($scriptTask1)
            ->addActivity($scriptTask2);
        $process
            ->addEvent($start)
            ->addEvent($end);

        //flows
        $start->createFlowTo($scriptTask1, $this->flowRepository);
        $scriptTask1->createFlowTo($scriptTask2, $this->flowRepository);
        $scriptTask2->createFlowTo($end, $this->flowRepository);

        return $process;
    }
}


