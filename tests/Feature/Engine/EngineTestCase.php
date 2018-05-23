<?php

namespace Tests\Feature\Engine;

use PHPUnit\Framework\TestCase;
use ProcessMaker\Bpmn\TestEngine;
use ProcessMaker\Models\RepositoryFactory;
use ProcessMaker\Nayra\Contracts\Bpmn\FlowElementInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\IntermediateCatchEventInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\IntermediateTimerEventInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\TimerEventDefinitionInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\TokenInterface;
use ProcessMaker\Nayra\Contracts\Engine\EngineInterface;
use ProcessMaker\Nayra\Contracts\Engine\JobManagerInterface;
use ProcessMaker\Nayra\Contracts\EventBusInterface;

/**
 * Test transitions
 *
 */
class EngineTestCase extends TestCase
{
    /**
     *
     * @var \ProcessMaker\Models\ProcessRepository
     */
    protected $processRepository;

    /**
     *
     * @var \ProcessMaker\Models\ActivityRepository
     */
    protected $activityRepository;

    /**
     * @var \ProcessMaker\Models\EventRepository
     */
    protected $eventRepository;

    /**
     * @var \ProcessMaker\Models\DataStoreRepository
     */
    protected $dataStoreRepository;

    /**
     * @var \ProcessMaker\Models\GatewayRepository
     */
    protected $gatewayRepository;

    /**
     * @var \ProcessMaker\Models\FlowRepository
     */
    protected $flowRepository;

    /**
     * @var \ProcessMaker\Models\RootElementRepository
     */
    protected $rootElementRepository;

    /**
     * @var \ProcessMaker\Models\MessageFlowRepository
     */
    protected $messageFlowRepository;

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
     * Initialize the engine and the factories.
     *
     */
    protected function setUp()
    {
        parent::setUp();
        //Initialize the repository factory
        $factory = new RepositoryFactory();
        $this->processRepository = $factory->getProcessRepository();
        $this->activityRepository = $factory->getActivityRepository();
        $this->gatewayRepository = $factory->getGatewayRepository();
        $this->eventRepository = $factory->getEventRepository();
        $this->flowRepository = $factory->getFlowRepository();
        $this->dataStoreRepository = $factory->getDataStoreRepository();
        $this->rootElementRepository = $factory->getRootElementRepository();
        $this->messageFlowRepository = $factory->getMessageFlowRepository();

        //Initialize a dispatcher
        $fakeDispatcher = $this->getMockBuilder(EventBusInterface::class)
            ->getMock();

        $fakeDispatcher->expects($this->any())
            ->method('dispatch')
            ->will($this->returnCallback(function($event, $payload) {
                $this->firedEvents[] = $event;
                if (empty($this->listeners[$event])) {
                    return;
                }
                foreach($this->listeners[$event] as $listener) {
                    call_user_func_array($listener, $payload['arguments']);
                }
            }));

        $fakeDispatcher->expects($this->any())
            ->method('listen')
            ->will($this->returnCallback(function($event, $listener) {
                $this->listeners[$event][] = $listener;
            }));

        //Initialize the engine
        $this->engine = new TestEngine($factory, $fakeDispatcher);
        $this->engine->setRepositoryFactory($factory);
        $this->engine->setDispatcher($fakeDispatcher);

        //Mock a job manager
        $this->jobManager = $this->getMockBuilder(JobManagerInterface::class)
            ->getMock();

        $this->jobManager->expects($this->any())
            ->method('scheduleDate')
            ->will($this->returnCallback(function($date, TimerEventDefinitionInterface $timerDefinition, FlowElementInterface $element, TokenInterface $token = null) {
                    $this->jobs[] = [
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
                        'timer' => $duration,
                        'eventDefinition' => $timerDefinition,
                        'element' => $element,
                        'token' => $token,
                    ];
                }));

        //Link the jobs manager with the engine
        $this->engine->getDispatcher()->listen(
            JobManagerInterface::EVENT_SCHEDULE_DATE,
            function(TimerEventDefinitionInterface $timerDefinition, FlowElementInterface $element, TokenInterface $token = null) {
                $this->jobManager->scheduleDate("2018-05-22T20:47:38+00:00", $timerDefinition, $element, $token);
            }
        );
        $this->engine->getDispatcher()->listen(
            JobManagerInterface::EVENT_SCHEDULE_CYCLE,
            function(TimerEventDefinitionInterface $timerDefinition, FlowElementInterface $element, TokenInterface $token = null) {
                $this->jobManager->scheduleCycle("PT36H", $timerDefinition, $element, $token);
            }
        );
        $this->engine->getDispatcher()->listen(
            JobManagerInterface::EVENT_SCHEDULE_DURATION,
            function(TimerEventDefinitionInterface $timerDefinition, FlowElementInterface $element, TokenInterface $token = null) {
                $this->jobManager->scheduleDuration("00:20:00", $timerDefinition, $element, $token);
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
        $this->assertEquals(
            $events,
            $this->firedEvents,
            'Expected event was not fired'
        );
        $this->firedEvents = [];
    }

    /**
     * Assert that a date timer was scheduled.
     *
     * @param string $date
     * @param FlowElementInterface $element
     * @param TokenInterface $token
     */
    protected function assertScheduledDateTimer($date, FlowElementInterface $element, TokenInterface $token = null)
    {
        $found = false;
        foreach ($this->jobs as $job) {
            if (isset($job['date']) && $job['date'] === $date && $job['element'] === $element && $job['token'] === $token) {
                $found = true;
            }
        }
        $this->assertTrue($found);
    }

    /**
     * Assert that a cyclic timer was scheduled.
     *
     * @param string $cycle
     * @param FlowElementInterface $element
     * @param TokenInterface $token
     */
    protected function assertScheduledCyclicTimer($cycle, FlowElementInterface $element, TokenInterface $token = null)
    {
        $found = false;
        foreach ($this->jobs as $job) {
            if (isset($job['cycle']) && $job['cycle'] === $cycle && $job['element'] === $element && $job['token'] === $token) {
                $found = true;
            }
        }
        $this->assertTrue($found);
    }

    /**
     * Assert that a duration timer was scheduled.
     *
     * @param string $duration
     * @param FlowElementInterface $element
     * @param TokenInterface $token
     */
    protected function assertScheduledDurationTimer($duration, FlowElementInterface $element, TokenInterface $token = null)
    {
        $found = false;
        foreach ($this->jobs as $job) {
            if (isset($job['duration']) && $job['duration'] === $duration && $job['element'] === $element && $job['token'] === $token) {
                $found = true;
            }
        }
        $this->assertTrue($found);
    }

    /**
     * Helper to dispatch a job from the JobManager mock
     *
     */
    protected function dispatchJob()
    {
        $job = array_shift($this->jobs);
        return $job ? $job['element']->execute($job['timer'], $job['token']->getInstance()) : null;
    }
}
