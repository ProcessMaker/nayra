<?php
namespace Tests\Feature\Engine;
use PHPUnit\Framework\TestCase;
use ProcessMaker\Bpmn\TestEngine;
use ProcessMaker\Models\CallActivity;
use ProcessMaker\Nayra\Bpmn\Model\Activity;
use ProcessMaker\Nayra\Bpmn\Model\DataStore;
use ProcessMaker\Nayra\Bpmn\Model\EndEvent;
use ProcessMaker\Nayra\Bpmn\Model\ExclusiveGateway;
use ProcessMaker\Nayra\Bpmn\Model\Flow;
use ProcessMaker\Nayra\Bpmn\Model\InclusiveGateway;
use ProcessMaker\Nayra\Bpmn\Model\ParallelGateway;
use ProcessMaker\Nayra\Bpmn\Model\Process;
use ProcessMaker\Nayra\Bpmn\Model\ScriptTask;
use ProcessMaker\Nayra\Bpmn\Model\StartEvent;
use ProcessMaker\Nayra\Bpmn\Model\Token;
use ProcessMaker\Nayra\Bpmn\Models\Collaboration;
use ProcessMaker\Nayra\Bpmn\Models\Participant;
use ProcessMaker\Nayra\Bpmn\Models\TerminateEventDefinition;
use ProcessMaker\Nayra\Contracts\Bpmn\ActivityInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\CallActivityInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\CollaborationInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\DataStoreInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\EndEventInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\ExclusiveGatewayInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\FlowElementInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\FlowInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\InclusiveGatewayInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\ParallelGatewayInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\ParticipantInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\ProcessInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\ScriptTaskInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\StartEventInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\TerminateEventDefinitionInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\TimerEventDefinitionInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\TokenInterface;
use ProcessMaker\Nayra\Contracts\Engine\EngineInterface;
use ProcessMaker\Nayra\Contracts\Engine\JobManagerInterface;
use ProcessMaker\Nayra\Contracts\EventBusInterface;
use ProcessMaker\Nayra\Factory;

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
    protected $factory;

    /**
     * Initialize the engine and the factories.
     *
     */
    protected function setUp()
    {
        parent::setUp();
        $this->factory = $this->getFactory();
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
        $this->engine = new TestEngine($this->factory, $fakeDispatcher);
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
     * Get factory instance
     *
     * @return Factory
     */
    protected function getFactory() {

        $mappings = [
            ActivityInterface::class => Activity::class,
            StartEventInterface::class => StartEvent::class,
            EndEventInterface::class => EndEvent::class,
            ExclusiveGatewayInterface::class => ExclusiveGateway::class,
            InclusiveGatewayInterface::class => InclusiveGateway::class,
            ProcessInterface::class => Process::class,
            DataStoreInterface::class => DataStore::class,
            FlowInterface::class => Flow::class,
            TokenInterface::class => Token::class,
            CollaborationInterface::class => Collaboration::class,
            ParticipantInterface::class => Participant::class,
            ScriptTaskInterface::class => ScriptTask::class,
            CallActivityInterface::class => CallActivity::class,
            ParallelGatewayInterface::class => ParallelGateway::class,
            TerminateEventDefinitionInterface::class => TerminateEventDefinition::class,
        ];

        return new Factory($mappings);
    }
}
