<?php

namespace Tests\Feature\Engine;

use ProcessMaker\Nayra\Contracts\Bpmn\ActivityInterface;
use ProcessMaker\Nayra\Contracts\Engine\ExecutionInstanceInterface;
use ProcessMaker\Repositories\BpmnFileRepository;

/**
 * Test pools lane sets and lanes.
 *
 */
class LanesTest extends EngineTestCase
{

    /**
     * Test loading a collaboration with a single participant with two lanes.
     *
     */
    public function testLoadingLanes()
    {
        //Load a BpmnFile Repository
        $bpmnRepository = new BpmnFileRepository();
        $bpmnRepository->setEngine($this->engine);
        $bpmnRepository->load(__DIR__ . '/files/Lanes.bpmn');

        //Load a process from a bpmn repository by Id
        $process = $bpmnRepository->loadBpmElementById('PROCESS_1');

        //Assertion: The process has a collection of LaneSets
        $this->assertNotEmpty($process->getLaneSets());

        //Assertion: The loaded process has one LaneSet
        $this->assertNotEmpty(1, $process->getLaneSets()->count());

        //Assertion: The loaded LaneSet has two lanes
        $laneSet = $process->getLaneSets();
        $this->assertNotEmpty(2, $laneSet->getLanes()->count());
    }

    /**
     * Lanes have no effect on the execution and should be ignored.
     *
     */
    public function testExecutionWithLanes()
    {
        //Load a BpmnFile Repository
        $bpmnRepository = new BpmnFileRepository();
        $bpmnRepository->setEngine($this->engine);
        $bpmnRepository->load(__DIR__ . '/files/Lanes.bpmn');

        //Load a process from a bpmn repository by Id
        $process = $bpmnRepository->loadBpmElementById('PROCESS_1');

        //Execute the process
        $instance = $process->call();
        $this->engine->runToNextState();

        //Assertion: A process instance has started
        $this->assertEquals(1, $process->getInstances()->count());

        //Complete the Task A
        $taksA = $bpmnRepository->loadBpmElementById('_7');
        $this->completeTask($taksA, $instance);

        //Complete the Task B
        $taksB = $bpmnRepository->loadBpmElementById('_13');
        $this->completeTask($taksB, $instance);

        //Complete the Task C
        $taksC = $bpmnRepository->loadBpmElementById('_15');
        $this->completeTask($taksC, $instance);

        //Complete the Task D
        $taksD = $bpmnRepository->loadBpmElementById('_9');
        $this->completeTask($taksD, $instance);
    }

    /**
     * Helper to complete a task.
     *
     * @param ActivityInterface $task
     * @param ExecutionInstanceInterface $instance
     */
    private function completeTask(ActivityInterface $task, ExecutionInstanceInterface $instance)
    {
        $token = $task->getTokens($instance)->item(0);
        $task->complete($token);
        $this->engine->runToNextState();
    }
}
