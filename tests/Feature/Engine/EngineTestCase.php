<?php
namespace Tests\Feature\Engine;
use PHPUnit\Framework\TestCase;
use ProcessMaker\Bpmn\TestEngine;
use ProcessMaker\Models\CallActivity;
use ProcessMaker\Nayra\Bpmn\Lane;
use ProcessMaker\Nayra\Bpmn\LaneSet;
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
use ProcessMaker\Nayra\Bpmn\Models\ConditionalEventDefinition;
use ProcessMaker\Nayra\Bpmn\Models\Error;
use ProcessMaker\Nayra\Bpmn\Models\ErrorEventDefinition;
use ProcessMaker\Nayra\Bpmn\Models\IntermediateCatchEvent;
use ProcessMaker\Nayra\Bpmn\Models\IntermediateThrowEvent;
use ProcessMaker\Nayra\Bpmn\Models\ItemDefinition;
use ProcessMaker\Nayra\Bpmn\Models\Message;
use ProcessMaker\Nayra\Bpmn\Models\MessageEventDefinition;
use ProcessMaker\Nayra\Bpmn\Models\MessageFlow;
use ProcessMaker\Nayra\Bpmn\Models\Participant;
use ProcessMaker\Nayra\Bpmn\Models\Signal;
use ProcessMaker\Nayra\Bpmn\Models\SignalEventDefinition;
use ProcessMaker\Nayra\Bpmn\Models\TerminateEventDefinition;
use ProcessMaker\Nayra\Bpmn\Models\TimerEventDefinition;
use ProcessMaker\Nayra\Bpmn\Models\Operation;
use ProcessMaker\Nayra\Contracts\Bpmn\ActivityInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\CallActivityInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\CollaborationInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\ConditionalEventDefinitionInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\DataStoreInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\EndEventInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\ErrorEventDefinitionInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\ErrorInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\ExclusiveGatewayInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\FlowElementInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\FlowInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\FormalExpressionInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\InclusiveGatewayInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\IntermediateCatchEventInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\IntermediateThrowEventInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\ItemDefinitionInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\LaneInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\LaneSetInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\MessageEventDefinitionInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\MessageFlowInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\MessageInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\ParallelGatewayInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\ParticipantInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\ProcessInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\ScriptTaskInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\SignalEventDefinitionInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\SignalInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\StartEventInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\TerminateEventDefinitionInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\TimerEventDefinitionInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\TokenInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\OperationInterface;
use ProcessMaker\Nayra\Contracts\Engine\EngineInterface;
use ProcessMaker\Nayra\Contracts\Engine\ExecutionInstanceInterface;
use ProcessMaker\Nayra\Contracts\Engine\JobManagerInterface;
use ProcessMaker\Nayra\Contracts\EventBusInterface;
use ProcessMaker\Nayra\Engine\ExecutionInstance;
use ProcessMaker\Nayra\Factory;
use ProcessMaker\Test\FormalExpression;

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
            CallActivityInterface::class => CallActivity::class,
            CollaborationInterface::class => Collaboration::class,
            ConditionalEventDefinitionInterface::class => ConditionalEventDefinition::class,
            DataStoreInterface::class => DataStore::class,
            EndEventInterface::class => EndEvent::class,
            ErrorEventDefinitionInterface::class => ErrorEventDefinition::class,
            ErrorInterface::class => Error::class,
            ExclusiveGatewayInterface::class => ExclusiveGateway::class,
            ExecutionInstanceInterface::class => ExecutionInstance::class,
            FlowInterface::class => Flow::class,
            FormalExpressionInterface::class => FormalExpression::class,
            InclusiveGatewayInterface::class => InclusiveGateway::class,
            IntermediateCatchEventInterface::class => IntermediateCatchEvent::class,
            IntermediateThrowEventInterface::class => IntermediateThrowEvent::class,
            ItemDefinitionInterface::class => ItemDefinition::class,
            MessageEventDefinitionInterface::class => MessageEventDefinition::class,
            MessageInterface::class => Message::class,
            MessageFlowInterface::class => MessageFlow::class,
            ParallelGatewayInterface::class => ParallelGateway::class,
            ParticipantInterface::class => Participant::class,
            ProcessInterface::class => Process::class,
            ScriptTaskInterface::class => ScriptTask::class,
            SignalEventDefinitionInterface::class => SignalEventDefinition::class,
            SignalInterface::class => Signal::class,
            StartEventInterface::class => StartEvent::class,
            TerminateEventDefinitionInterface::class => TerminateEventDefinition::class,
            TimerEventDefinitionInterface::class => TimerEventDefinition::class,
            TokenInterface::class => Token::class,
            OperationInterface::class => Operation::class,
            LaneSetInterface::class => LaneSet::class,
            LaneInterface::class => Lane::class,
        ];
        return new Factory($mappings);
    }
}
