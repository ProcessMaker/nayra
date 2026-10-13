<?php

namespace Tests\Feature\Engine;

use ProcessMaker\Nayra\Contracts\Bpmn\ActivityInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\IntermediateThrowEventInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\ProcessInterface;

/**
 * FOUR-6787: an Intermediate Throw Event with two incoming sequence flows must
 * fire for each arriving token, instead of waiting until every incoming flow has a token.
 *
 * Process: Start -> ParallelGateway -> ActivityA, ActivityB -> IntermediateThrowEvent -> End
 */
class IntermediateThrowMergeTest extends EngineTestCase
{
    /**
     * Build the process described above.
     *
     * @return array{0: \ProcessMaker\Nayra\Contracts\Bpmn\ProcessInterface, 1: array}
     */
    private function createMergeProcess(): array
    {
        $process = $this->repository->createProcess();
        $process->setEngine($this->engine);
        $process->setRepository($this->repository);

        $start = $this->repository->createStartEvent();
        $gateway = $this->repository->createParallelGateway();
        $activityA = $this->repository->createActivity();
        $activityB = $this->repository->createActivity();
        $throw = $this->repository->createIntermediateThrowEvent();
        $end = $this->repository->createEndEvent();

        $start->createFlowTo($gateway, $this->repository);
        $gateway->createFlowTo($activityA, $this->repository);
        $gateway->createFlowTo($activityB, $this->repository);
        $activityA->createFlowTo($throw, $this->repository);
        $activityB->createFlowTo($throw, $this->repository);
        $throw->createFlowTo($end, $this->repository);

        $process->addEvent($start)
            ->addEvent($throw)
            ->addEvent($end)
            ->addGateway($gateway)
            ->addActivity($activityA)
            ->addActivity($activityB);

        return [$process, [$start, $activityA, $activityB, $throw, $end]];
    }

    /**
     * An arriving token from one incoming flow must pass the throw event
     * without waiting for the other incoming flow.
     */
    public function testThrowEventPassesSingleArrivingToken()
    {
        [$process, [$start, $activityA, $activityB, $throw, $end]] = $this->createMergeProcess();

        $dataStore = $this->repository->createDataStore();
        $instance = $this->engine->createExecutionInstance($process, $dataStore);

        $start->start($instance);
        $this->engine->runToNextState();

        // Only ActivityA finishes; ActivityB still has its token.
        $tokenA = $activityA->getTokens($instance)->item(0);
        $activityA->complete($tokenA);
        $this->engine->runToNextState();

        // FOUR-6787 expectation: the throw event passes the token from ActivityA now.
        $this->assertContains(IntermediateThrowEventInterface::EVENT_THROW_TOKEN_PASSED, $this->firedEvents);
        $this->assertContains(ActivityInterface::EVENT_ACTIVITY_ACTIVATED, $this->firedEvents);
        $this->firedEvents = [];

        // Finishing ActivityB later fires the throw event again for its own token.
        $tokenB = $activityB->getTokens($instance)->item(0);
        $activityB->complete($tokenB);
        $this->engine->runToNextState();

        $this->assertContains(IntermediateThrowEventInterface::EVENT_THROW_TOKEN_PASSED, $this->firedEvents);
        $this->assertContains(ProcessInterface::EVENT_PROCESS_INSTANCE_COMPLETED, $this->firedEvents);
    }
}
