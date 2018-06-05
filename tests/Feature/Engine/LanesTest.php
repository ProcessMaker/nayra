<?php

namespace Tests\Feature\Engine;

use ProcessMaker\Nayra\Contracts\Bpmn\ActivityInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\EndEventInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\ProcessInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\StartEventInterface;
use ProcessMaker\Nayra\Contracts\Engine\ExecutionInstanceInterface;
use ProcessMaker\Nayra\Storage\BpmnDocument;
use ProcessMaker\Repositories\BpmnFileRepository;

/**
 * Test pools lane sets and lanes.
 *
 */
class LanesTest extends EngineTestCase
{

    /**
     * Test loading a collaboration with a single participant with two lanes
     * and a child lane set with two lanes.
     *
     */
    public function testLoadingLanes()
    {
        //Load a BpmnFile Repository
        $bpmnRepository = new BpmnDocument();
        $bpmnRepository->setEngine($this->engine);
        $bpmnRepository->setFactory($this->factory);

        $bpmnRepository->load(__DIR__ . '/files/Lanes.bpmn');

        //Load a process from a bpmn repository by Id
        $process = $bpmnRepository->getProcess('PROCESS_1');

        //Assertion: The process has a collection of LaneSets
        $this->assertNotEmpty($process->getLaneSets());

        //Assertion: The loaded process has one LaneSet
        $this->assertEquals(1, $process->getLaneSets()->count());

        //Assertion: The loaded LaneSet has three lanes
        $laneSet = $process->getLaneSets()->item(0);
        $this->assertEquals(3, $laneSet->getLanes()->count());

        //Assertion: The first lane has 3 flow nodes
        $firstLane = $laneSet->getLanes()->item(0);
        $this->assertEquals(3, $firstLane->getFlowNodes()->count());

        //Assertion: The second lane has 3 flow nodes
        $secondLane = $laneSet->getLanes()->item(1);
        $this->assertEquals(3, $secondLane->getFlowNodes()->count());

        //Assertion: The third lane has one child lane with two lanes
        $thirdLane = $laneSet->getLanes()->item(2);
        $this->assertEquals(1, $thirdLane->getChildLaneSets()->count());

        $childLaneSet = $thirdLane->getChildLaneSets()->item(0);
        $this->assertEquals(2, $childLaneSet->getLanes()->count());

        //Assertion: Verify the name of the lane set.
        $this->assertEquals('Process Lane Set', $laneSet->getName());

        //Assertion: Verify the name of the lanes.
        $this->assertEquals('Lane 1', $firstLane->getName());
        $this->assertEquals('Lane 2', $secondLane->getName());
        $this->assertEquals('Lane 3', $thirdLane->getName());

    }

    /**
     * Lanes have no effect on the execution and should be ignored.
     *
     */
    public function testExecutionWithLanes()
    {
        //Load a BpmnFile Repository
        $bpmnRepository = new BpmnDocument();
        $bpmnRepository->setEngine($this->engine);
        $bpmnRepository->setFactory($this->factory);
        $bpmnRepository->load(__DIR__ . '/files/Lanes.bpmn');

        //Load a process from a bpmn repository by Id
        $process = $bpmnRepository->getProcess('PROCESS_1');

        //Execute the process
        $instance = $process->call();
        $this->engine->runToNextState();

        //Assertion: A process instance has started
        $this->assertEquals(1, $process->getInstances()->count());

        //Complete the Task A
        $taksA = $bpmnRepository->getActivity('_7');
        $this->completeTask($taksA, $instance);

        //Complete the Task B
        $taksB = $bpmnRepository->getActivity('_13');
        $this->completeTask($taksB, $instance);

        //Complete the Task C
        $taksC = $bpmnRepository->getActivity('_15');
        $this->completeTask($taksC, $instance);

        //Complete the Task D
        $taksD = $bpmnRepository->getActivity('_9');
        $this->completeTask($taksD, $instance);

        //Assertion: All the process was executed
        $this->assertEvents([
            ProcessInterface::EVENT_PROCESS_INSTANCE_CREATED,
            StartEventInterface::EVENT_EVENT_TRIGGERED,
            ActivityInterface::EVENT_ACTIVITY_ACTIVATED,
            ActivityInterface::EVENT_ACTIVITY_COMPLETED,
            ActivityInterface::EVENT_ACTIVITY_CLOSED,
            ActivityInterface::EVENT_ACTIVITY_ACTIVATED,
            ActivityInterface::EVENT_ACTIVITY_COMPLETED,
            ActivityInterface::EVENT_ACTIVITY_CLOSED,
            ActivityInterface::EVENT_ACTIVITY_ACTIVATED,
            ActivityInterface::EVENT_ACTIVITY_COMPLETED,
            ActivityInterface::EVENT_ACTIVITY_CLOSED,
            ActivityInterface::EVENT_ACTIVITY_ACTIVATED,
            ActivityInterface::EVENT_ACTIVITY_COMPLETED,
            ActivityInterface::EVENT_ACTIVITY_CLOSED,
            EndEventInterface::EVENT_THROW_TOKEN_ARRIVES,
            EndEventInterface::EVENT_THROW_TOKEN_CONSUMED,
            EndEventInterface::EVENT_EVENT_TRIGGERED,
            ProcessInterface::EVENT_PROCESS_INSTANCE_COMPLETED,
        ]);
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
