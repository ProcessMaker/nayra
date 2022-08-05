<?php

namespace Tests\Feature\Engine;

use ProcessMaker\Nayra\Bpmn\Models\Process;
use ProcessMaker\Nayra\Contracts\Bpmn\ActivityInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\EndEventInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\EventInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\ProcessInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\ScriptTaskInterface;

/**
 * Tests for the ScriptTask element
 */
class ScriptTaskTest extends EngineTestCase
{
    // String with the name of the property that will be set/and read to test that a script task has been executed.
    // The testing script will set the property with this name of the ScriptTask to which it pertains
    const TEST_PROPERTY = 'scriptTestTaskProp';

    /**
     * Tests the a process with the sequence start->Task1->scriptTask1->End executes correctly
     */
    public function testProcessWithOneScriptTask()
    {
        //Load a process
        $process = $this->getProcessWithOneScriptTask();
        $dataStore = $this->repository->createDataStore();

        //create an instance of the process
        $instance = $this->engine->createExecutionInstance($process, $dataStore);

        //Get References
        $start = $process->getEvents()->item(0);
        $activity1 = $process->getActivities()->item(0);
        $scriptTask = $process->getActivities()->item(1);

        //start the process an instance of the process
        $start->start($instance);
        $this->engine->runToNextState();

        //Assert: that the process is stared and the first activity activated
        $this->assertEvents([
            ProcessInterface::EVENT_PROCESS_INSTANCE_CREATED,
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
            ScriptTaskInterface::EVENT_SCRIPT_TASK_ACTIVATED,
        ]);

        $token = $scriptTask->getTokens($instance)->item(0);
        $scriptTask->runScript($token);
        $scriptTask->complete($token);
        $this->engine->runToNextState();

        $this->assertEvents([
            ActivityInterface::EVENT_ACTIVITY_COMPLETED,
            ActivityInterface::EVENT_ACTIVITY_CLOSED,
            EndEventInterface::EVENT_THROW_TOKEN_ARRIVES,
            EndEventInterface::EVENT_THROW_TOKEN_CONSUMED,
            EndEventInterface::EVENT_EVENT_TRIGGERED,
            ProcessInterface::EVENT_PROCESS_INSTANCE_COMPLETED,
        ]);

        $this->assertEquals($scriptTask->getProperty(self::TEST_PROPERTY), 1);
    }

    /**
     * Tests that a process with the sequence start->ScriptTask1->scriptTask2->End runs correctly
     */
    public function testProcessWithScriptTasksOnly()
    {
        //Load a process
        $process = $this->getProcessWithOnlyScriptTasks();
        $dataStore = $this->repository->createDataStore();

        //create an instance of the process
        $instance = $this->engine->createExecutionInstance($process, $dataStore);

        //Get References
        $start = $process->getEvents()->item(0);
        $scriptTask1 = $process->getActivities()->item(0);
        $scriptTask2 = $process->getActivities()->item(1);

        //start the process an instance of the process
        $start->start($instance);
        $this->engine->runToNextState();

        //Assertion: all activities should run and the process finish immediately
        $this->assertEvents([
            ProcessInterface::EVENT_PROCESS_INSTANCE_CREATED,
            EventInterface::EVENT_EVENT_TRIGGERED,
            ActivityInterface::EVENT_ACTIVITY_ACTIVATED,
            ScriptTaskInterface::EVENT_SCRIPT_TASK_ACTIVATED,
        ]);

        $token = $scriptTask1->getTokens($instance)->item(0);
        $scriptTask1->runScript($token);
        $scriptTask1->complete($token);
        $this->engine->runToNextState();

        $this->assertEvents([
            ActivityInterface::EVENT_ACTIVITY_COMPLETED,
            ActivityInterface::EVENT_ACTIVITY_CLOSED,
            ActivityInterface::EVENT_ACTIVITY_ACTIVATED,
            ScriptTaskInterface::EVENT_SCRIPT_TASK_ACTIVATED,
        ]);

        $token = $scriptTask2->getTokens($instance)->item(0);
        $scriptTask2->runScript($token);
        $scriptTask2->complete($token);
        $this->engine->runToNextState();

        $this->assertEvents([
            ActivityInterface::EVENT_ACTIVITY_COMPLETED,
            ActivityInterface::EVENT_ACTIVITY_CLOSED,
            EndEventInterface::EVENT_THROW_TOKEN_ARRIVES,
            EndEventInterface::EVENT_THROW_TOKEN_CONSUMED,
            EndEventInterface::EVENT_EVENT_TRIGGERED,
            ProcessInterface::EVENT_PROCESS_INSTANCE_COMPLETED,
        ]);

        // Assertion: The first script task should be executed.
        // This is done by testing that the TEST_PROPERTY of the scriptTask1 was set
        $this->assertEquals($scriptTask1->getProperty(self::TEST_PROPERTY), 1);

        // Assertion: The second script task should be executed.
        // This is done by testing that the TEST_PROPERTY of the scriptTask2 was set
        $this->assertEquals($scriptTask2->getProperty(self::TEST_PROPERTY), 1);
    }

    /**
     * Tests that when a script fails, the ScriptTask token is set to failed status
     */
    public function testScriptTaskThatFails()
    {
        //Load a process
        $process = $this->getProcessWithOneScriptTask();
        $dataStore = $this->repository->createDataStore();

        //create an instance of the process
        $instance = $this->engine->createExecutionInstance($process, $dataStore);

        //Get References
        $start = $process->getEvents()->item(0);
        $activity1 = $process->getActivities()->item(0);
        $scriptTask = $process->getActivities()->item(1);

        //set an script that evaluates with an error
        $scriptTask->setScript('throw new Exception ("test exception");');

        //start the process an instance of the process
        $start->start($instance);
        $this->engine->runToNextState();

        //Assert: that the process is stared and the first activity activated
        $this->assertEvents([
            ProcessInterface::EVENT_PROCESS_INSTANCE_CREATED,
            EventInterface::EVENT_EVENT_TRIGGERED,
            ActivityInterface::EVENT_ACTIVITY_ACTIVATED,
        ]);

        //complete the first activity (that is a manual one)
        $token = $activity1->getTokens($instance)->item(0);
        $activity1->complete($token);
        $this->engine->runToNextState();

        $scriptToken = $scriptTask->getTokens($instance)->item(0);
        $scriptTask->runScript($scriptToken);

        //Assertion: Verify that the token was set to a failed state
        $this->assertEquals($scriptToken->getStatus(), ActivityInterface::TOKEN_STATE_FAILING);
    }

    /**
     * Generates a process with the following elements: start->Task1->scriptTask1->End
     * @return \ProcessMaker\Models\Process
     */
    private function getProcessWithOneScriptTask()
    {
        $process = $this->repository->createProcess();
        $process->setEngine($this->engine);

        //elements
        $start = $this->repository->createStartEvent();
        $activityA = $this->repository->createActivity();
        $scriptTask = $this->repository->createScriptTask();
        $scriptTask->setScriptFormat('text/php');
        $scriptTask->setScript('$this->setProperty("'.self::TEST_PROPERTY.'", 1);');
        $end = $this->repository->createEndEvent();

        $process
            ->addActivity($activityA)
            ->addActivity($scriptTask);
        $process
            ->addEvent($start)
            ->addEvent($end);

        //flows
        $start->createFlowTo($activityA, $this->repository);
        $activityA->createFlowTo($scriptTask, $this->repository);
        $scriptTask->createFlowTo($end, $this->repository);

        return $process;
    }

    /**
     * Generates a process with the following elements: start->scriptTask1->scriptTask2->End
     * @return \ProcessMaker\Models\Process
     */
    private function getProcessWithOnlyScriptTasks()
    {
        $process = $this->repository->createProcess();
        $process->setEngine($this->engine);

        //elements
        $start = $this->repository->createStartEvent();
        $scriptTask1 = $this->repository->createScriptTask();
        $scriptTask2 = $this->repository->createScriptTask();

        $scriptTask1->setScriptFormat('text/php');
        $scriptTask1->setScript('$this->setProperty("scriptTestTaskProp", 1);');

        $scriptTask2->setScriptFormat('text/php');
        $scriptTask2->setScript('$this->setProperty("scriptTestTaskProp", 1);');

        $end = $this->repository->createEndEvent();

        $process
            ->addActivity($scriptTask1)
            ->addActivity($scriptTask2);
        $process
            ->addEvent($start)
            ->addEvent($end);

        //flows
        $start->createFlowTo($scriptTask1, $this->repository);
        $scriptTask1->createFlowTo($scriptTask2, $this->repository);
        $scriptTask2->createFlowTo($end, $this->repository);

        return $process;
    }

    /**
     * Tests that when a script fails, then it is closed
     */
    public function testScriptTaskThatFailsAndIsClosed()
    {
        //Load a process
        $process = $this->getProcessWithOneScriptTask();
        $dataStore = $this->repository->createDataStore();

        //create an instance of the process
        $instance = $this->engine->createExecutionInstance($process, $dataStore);

        //Get References
        $start = $process->getEvents()->item(0);
        $activity1 = $process->getActivities()->item(0);
        $scriptTask = $process->getActivities()->item(1);

        //set an script that evaluates with an error
        $scriptTask->setScript('throw new Exception ("test exception");');

        //start the process an instance of the process
        $start->start($instance);
        $this->engine->runToNextState();

        //Assert: that the process is stared and the first activity activated
        $this->assertEvents([
            ProcessInterface::EVENT_PROCESS_INSTANCE_CREATED,
            EventInterface::EVENT_EVENT_TRIGGERED,
            ActivityInterface::EVENT_ACTIVITY_ACTIVATED,
        ]);

        //complete the first activity (that is a manual one)
        $token = $activity1->getTokens($instance)->item(0);
        $activity1->complete($token);
        $this->engine->runToNextState();

        $scriptToken = $scriptTask->getTokens($instance)->item(0);
        $scriptTask->runScript($scriptToken);
        $this->engine->runToNextState();

        //Assertion: Verify that the token was set to a failed state
        $this->assertEquals($scriptToken->getStatus(), ActivityInterface::TOKEN_STATE_FAILING);

        // Close the script task
        $scriptToken = $scriptTask->getTokens($instance)->item(0);
        $scriptToken->setStatus(ActivityInterface::TOKEN_STATE_CLOSED);
        $this->engine->runToNextState();

        //Assertion: Verify that the script was
        $this->assertEvents([
            ActivityInterface::EVENT_ACTIVITY_COMPLETED,
            ActivityInterface::EVENT_ACTIVITY_CLOSED,
            ActivityInterface::EVENT_ACTIVITY_ACTIVATED,
            ScriptTaskInterface::EVENT_SCRIPT_TASK_ACTIVATED,
            ActivityInterface::EVENT_ACTIVITY_EXCEPTION,
            ActivityInterface::EVENT_ACTIVITY_CANCELLED,
            ProcessInterface::EVENT_PROCESS_INSTANCE_COMPLETED,
        ]);
    }
}
