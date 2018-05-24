<?php

namespace Tests\Feature\Engine;

use ProcessMaker\Nayra\Contracts\Bpmn\ActivityInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\EndEventInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\EventInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\GatewayInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\ProcessInterface;

/**
 * Test transitions
 *
 */
class ScriptTaskTest extends EngineTestCase
{
    public function testProcessWithOneScriptTask()
    {
        //Load a process with the configuration:w
        //start->Task1->scriptTask1->End
        $process = $this->getProcessWithOneScriptTask();

        //create an instance of the process
        $instance = $process->createInstance();

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
            EndEventInterface::EVENT_EVENT_TRIGGERED,
            ProcessInterface::EVENT_PROCESS_COMPLETED,
        ]);

        $this->assertScriptTaskExecuted($scriptTask);
    }

    public function testProcessWithScriptTasksOnly()
    {
        //Load a process with the configuration:w
        //start->Task1->scriptTask1->End
        $process = $this->getProcessWithOnlyScriptTasks();

        //create an instance of the process
        $instance = $process->createInstance();

        //Get References
        $start = $process->getEvents()->item(0);
        $activity1 = $process->getActivities()->item(0);
        $scriptTask = $process->getActivities()->item(1);

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
            EndEventInterface::EVENT_EVENT_TRIGGERED,
            ProcessInterface::EVENT_PROCESS_COMPLETED,
        ]);

        $this->assertScriptTaskExecuted($scriptTask);
    }
}


