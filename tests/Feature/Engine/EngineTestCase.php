<?php
namespace Tests\Feature\Engine;

use PHPUnit\Framework\TestCase;
use ProcessMaker\Bpmn\TestEngine;
use ProcessMaker\Nayra\Bpmn\Models\IntermediateCatchEvent;
use ProcessMaker\Nayra\Bpmn\Models\IntermediateThrowEvent;
use ProcessMaker\Nayra\Contracts\Bpmn\EndEventInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\FlowElementInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\GatewayInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\ThrowEventInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\TimerEventDefinitionInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\TokenInterface;
use ProcessMaker\Nayra\Contracts\Engine\EngineInterface;
use ProcessMaker\Nayra\Contracts\Engine\JobManagerInterface;
use ProcessMaker\Nayra\Contracts\EventBusInterface;
use ProcessMaker\Test\Models\Repository;

/**
 * Test transitions
 *
 */
class EngineTestCase extends TestCase
{
    /**
     *
     * @var EngineInterface
     */
    protected $engine;
    /**
     * Fired events during the test.
     *
     * @var array
     */
    protected $firedEvents = [];
    /**
     * Event listeners
     *
     * @var array
     */
    protected $listeners = [];
    /**
     * Scheduled jobs.
     *
     * @var array $jobs
     */
    protected $jobs = [];

    /**
     * Scheduled jobs.
     *
     * @var \ProcessMaker\Nayra\Contracts\FactoryInterface $factory
     */
    protected $repository;

    /**
     * List of of events that should be persisted by the engine
     *
     * @var array
     */
    protected $eventsToPersist = [
        ThrowEventInterface::EVENT_THROW_TOKEN_ARRIVES,
        ThrowEventInterface::EVENT_THROW_TOKEN_CONSUMED,
        ThrowEventInterface::EVENT_THROW_TOKEN_PASSED,
        GatewayInterface::EVENT_GATEWAY_TOKEN_ARRIVES,
        GatewayInterface::EVENT_GATEWAY_TOKEN_CONSUMED,
        GatewayInterface::EVENT_GATEWAY_TOKEN_PASSED,
        IntermediateCatchEvent::EVENT_CATCH_TOKEN_ARRIVES,
        IntermediateCatchEvent::EVENT_CATCH_TOKEN_CONSUMED,
        IntermediateCatchEvent::EVENT_CATCH_TOKEN_PASSED,
        EndEventInterface::EVENT_THROW_TOKEN_ARRIVES,
        EndEventInterface::EVENT_THROW_TOKEN_CONSUMED,
    ];

    /**
     * Initialize the engine and the factories.
     *
     */
    protected function setUp()
    {
        parent::setUp();
        $this->repository = $this->createRepository();
        //Initialize a dispatcher
        $fakeDispatcher = $this->getMockBuilder(EventBusInterface::class)
            ->getMock();
        $fakeDispatcher->expects($this->any())
            ->method('dispatch')
            ->will($this->returnCallback(function($event, ...$payload) {
                $this->firedEvents[] = $event;
                if (empty($this->listeners[$event])) {
                    return;
                }
                foreach($this->listeners[$event] as $listener) {
                    call_user_func_array($listener, $payload);
                }
            }));
        $fakeDispatcher->expects($this->any())
            ->method('listen')
            ->will($this->returnCallback(function($event, $listener) {
                $this->listeners[$event][] = $listener;
            }));
        //Initialize the engine
        $this->engine = new TestEngine($this->repository, $fakeDispatcher);
        //Mock a job manager
        $this->jobManager = $this->getMockBuilder(JobManagerInterface::class)
            ->getMock();
        $this->jobManager->expects($this->any())
            ->method('scheduleDate')
            ->will($this->returnCallback(function($date, TimerEventDefinitionInterface $timerDefinition, FlowElementInterface $element, TokenInterface $token = null) {
                $this->jobs[] = [
                    'repeat' => false,
                    'timer' => $date,
                    'eventDefinition' => $timerDefinition,
                    'element' => $element,
                    'token' => $token,
                ];
            }));
        $this->jobManager->expects($this->any())
            ->method('scheduleCycle')
            ->will($this->returnCallback(function($cycle, TimerEventDefinitionInterface $timerDefinition, FlowElementInterface $element, TokenInterface $token = null) {
                $this->jobs[] = [
                    'repeat' => true,
                    'timer' => $cycle,
                    'eventDefinition' => $timerDefinition,
                    'element' => $element,
                    'token' => $token,
                ];
            }));
        $this->jobManager->expects($this->any())
            ->method('scheduleDuration')
            ->will($this->returnCallback(function($duration, TimerEventDefinitionInterface $timerDefinition, FlowElementInterface $element, TokenInterface $token = null) {
                $this->jobs[] = [
                    'repeat' => false,
                    'timer' => $duration,
                    'eventDefinition' => $timerDefinition,
                    'element' => $element,
                    'token' => $token,
                ];
            }));
        //Link the jobs manager with the engine
        $this->engine->getDispatcher()->listen(
            JobManagerInterface::EVENT_SCHEDULE_DATE,
            function($date, TimerEventDefinitionInterface $timerDefinition, FlowElementInterface $element, TokenInterface $token = null) {
                $this->jobManager->scheduleDate($date, $timerDefinition, $element, $token);
            }
        );
        $this->engine->getDispatcher()->listen(
            JobManagerInterface::EVENT_SCHEDULE_CYCLE,
            function($cycle, TimerEventDefinitionInterface $timerDefinition, FlowElementInterface $element, TokenInterface $token = null) {
                $this->jobManager->scheduleCycle($cycle, $timerDefinition, $element, $token);
            }
        );
        $this->engine->getDispatcher()->listen(
            JobManagerInterface::EVENT_SCHEDULE_DURATION,
            function($duration, TimerEventDefinitionInterface $timerDefinition, FlowElementInterface $element, TokenInterface $token = null) {
                $this->jobManager->scheduleDuration($duration, $timerDefinition, $element, $token);
            }
        );
    }
    /**
     * Tear down the test case.
     *
     */
    protected function tearDown()
    {
        $this->engine->closeExecutionInstances();
        parent::tearDown();
    }
    /**
     * Assert that events were fired.
     *
     * @param array $events
     */
    protected function assertEvents(array $events)
    {
        $tokenRepository = $this->engine->getRepository()->getTokenRepository();
        $this->assertRepositoryCalls($tokenRepository->getPersistCalls());
        $tokenRepository->resetPersistCalls();

        $this->assertEquals(
            $events,
            $this->firedEvents,
            'Expected event was not fired'
        );
        $this->firedEvents = [];
    }

    protected function assertRepositoryCalls ($expectedCalls) {
        $count = 0;
        foreach($this->firedEvents as $event) {
            $count += (in_array($event, $this->eventsToPersist)) ? 1 : 0;
        }

        $this->assertEquals($expectedCalls, $count);
    }

    /**
     * Assert that a date timer was scheduled.
     *
     * @param string $date
     * @param FlowElementInterface $element
     * @param TokenInterface|null $token
     */
    protected function assertScheduledDateTimer($date, FlowElementInterface $element, TokenInterface $token = null)
    {
        $found = false;
        $scheduled = [];
        foreach ($this->jobs as $job) {
            $scheduled[] = $job['timer'];
            if (isset($job['timer']) && $job['timer'] === $date && $job['element'] === $element && $job['token'] === $token) {
                $found = true;
            }
        }
        $this->assertTrue($found, "Failed asserting that a date timer ($date) was scheduled: " . json_encode($scheduled));
    }
    /**
     * Assert that a cyclic timer was scheduled.
     *
     * @param string $cycle
     * @param FlowElementInterface $element
     * @param TokenInterface|null $token
     */
    protected function assertScheduledCyclicTimer($cycle, FlowElementInterface $element, TokenInterface $token = null)
    {
        $found = false;
        $scheduled = [];
        foreach ($this->jobs as $job) {
            $scheduled[] = $job['timer'];
            if (isset($job['timer']) && $job['timer'] === $cycle && $job['element'] === $element && $job['token'] === $token) {
                $found = true;
            }
        }
        $this->assertTrue($found, "Failed asserting that a cycle timer ($cycle) was scheduled: " . json_encode($scheduled));
    }
    /**
     * Assert that a duration timer was scheduled.
     *
     * @param string $duration
     * @param FlowElementInterface $element
     * @param TokenInterface|null $token
     */
    protected function assertScheduledDurationTimer($duration, FlowElementInterface $element, TokenInterface $token = null)
    {
        $found = false;
        foreach ($this->jobs as $job) {
            if (isset($job['timer']) && $job['timer'] === $duration && $job['element'] === $element && $job['token'] === $token) {
                $found = true;
            }
        }
        $this->assertTrue($found, 'Failed asserting that a duration timer was scheduled');
    }
    /**
     * Helper to dispatch a job from the JobManager mock
     *
     */
    protected function dispatchJob()
    {
        $job = array_shift($this->jobs);
        if ($job && $job['repeat']) {
            $this->jobs[] = $job;
        }
        $instance = $job['token'] ? $job['token']->getInstance() : null;
        return $job ? $job['element']->execute($job['eventDefinition'], $instance) : null;
    }

    /**
     * Get repository instance
     *
     * @return \ProcessMaker\Nayra\Contracts\RepositoryInterface
     */
    private function createRepository()
    {
        return new Repository();
    }
}
