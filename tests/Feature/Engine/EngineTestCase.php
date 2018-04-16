<?php

namespace Tests\Feature\Engine;

use PHPUnit\Framework\TestCase;
use ProcessMaker\Nayra\Contracts\Engine\EngineInterface;
use ProcessMaker\Bpmn\TestEngine;
use ProcessMaker\Models\RepositoryFactory;
use ProcessMaker\Models\ItemDefinitionFactory;

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
        $fakeDispatcher = $this->getMockBuilder(\Illuminate\Contracts\Events\Dispatcher::class)
            ->getMock();
        $fakeDispatcher->expects($this->any())
            ->method('dispatch')
            ->will($this->returnCallback(function($event, $payload) {
                $this->firedEvents[] = $event;
            }));

        //Initialize the engine
        $this->engine = new TestEngine($factory, $fakeDispatcher);
        $this->engine->setRepositoryFactory($factory);
        $this->engine->setDispatcher($fakeDispatcher);
    }

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
}
