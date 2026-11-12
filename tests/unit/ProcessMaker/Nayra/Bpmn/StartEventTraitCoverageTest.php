<?php

namespace ProcessMaker\Nayra\Bpmn;

use PHPUnit\Framework\TestCase;
use ProcessMaker\Bpmn\TestEngine;
use ProcessMaker\Nayra\Bpmn\Models\Activity;
use ProcessMaker\Nayra\Bpmn\Models\EndEvent;
use ProcessMaker\Nayra\Bpmn\Models\Message;
use ProcessMaker\Nayra\Bpmn\Models\MessageEventDefinition;
use ProcessMaker\Nayra\Bpmn\Models\Process;
use ProcessMaker\Nayra\Bpmn\Models\StartEvent;
use ProcessMaker\Nayra\Bpmn\Models\SubProcess;
use ProcessMaker\Nayra\Contracts\Bpmn\EventDefinitionInterface;
use ProcessMaker\Nayra\Contracts\EventBusInterface;
use ProcessMaker\Test\Models\ExecutionInstance;
use ProcessMaker\Test\Models\Repository;

/**
 * Cover remaining StartEventTrait branches.
 */
class StartEventTraitCoverageTest extends TestCase
{
    public function testGetInputPlaceReturnsNull()
    {
        $start = new StartEvent();
        $this->assertNull($start->getInputPlace());
    }

    public function testIsSubscribedForInstanceOutsideEventSubProcess()
    {
        $start = new StartEvent();
        $instance = new ExecutionInstance();
        $this->assertTrue($start->isSubscribedForInstance($instance));
    }

    public function testIsSubscribedForInstanceInsideEventSubProcess()
    {
        $eventSub = new SubProcess();
        $eventSub->setTriggeredByEvent(true);
        $start = new StartEvent();
        $eventSub->addFlowElement($start);

        $empty = new ExecutionInstance();
        $this->assertFalse($start->isSubscribedForInstance($empty));

        $active = new ExecutionInstance();
        $token = $this->createMock(\ProcessMaker\Nayra\Contracts\Bpmn\TokenInterface::class);
        $active->getTokens()->push($token);
        $this->assertTrue($start->isSubscribedForInstance($active));
    }

    public function testExecuteCreatesInstanceWhenNull()
    {
        $repo = new Repository();
        $engine = $this->createEngine($repo);
        $process = new Process();
        $process->setEngine($engine);
        $process->setRepository($repo);

        $message = new Message();
        $message->setId('Msg');
        $definition = new MessageEventDefinition();
        $definition->setPayload($message);

        $start = new StartEvent();
        $start->setId('MsgStart');
        $start->getEventDefinitions()->push($definition);
        $task = new Activity();
        $end = new EndEvent();
        $process->addActivity($task)->addEvent($start)->addEvent($end);
        $start->createFlowTo($task, $repo);
        $task->createFlowTo($end, $repo);
        $engine->loadProcess($process);

        $thrown = new MessageEventDefinition();
        $thrown->setPayload($message);
        $start->execute($thrown, null, null);
        $engine->runToNextState();

        $this->assertEquals(1, $process->getInstances()->count());
        $this->assertEquals(1, $task->getTokens($process->getInstances()->item(0))->count());
    }

    public function testExecuteSkipsWhenAssertsRuleFails()
    {
        $repo = new Repository();
        $engine = $this->createEngine($repo);
        $process = new Process();
        $process->setEngine($engine);
        $process->setRepository($repo);

        $definition = $this->createMock(EventDefinitionInterface::class);
        $definition->method('assertsRule')->willReturn(false);
        $definition->method('getId')->willReturn('Def1');

        $start = new StartEvent();
        $start->getEventDefinitions()->push($definition);
        $process->addEvent($start);
        $start->buildTransitions($repo);

        $result = $start->execute($this->createMock(EventDefinitionInterface::class));
        $this->assertSame($start, $result);
        $this->assertEquals(0, $process->getInstances()->count());
    }

    public function testEventSubProcessStartWithExplicitInstance()
    {
        $repo = new Repository();
        $engine = $this->createEngine($repo);
        $process = new Process();
        $process->setEngine($engine);
        $process->setRepository($repo);

        $message = new Message();
        $message->setId('Msg2');
        $definition = new MessageEventDefinition();
        $definition->setPayload($message);

        $eventSub = new SubProcess();
        $eventSub->setTriggeredByEvent(true);
        $eventSub->setOwnerProcess($process);
        $msgStart = new StartEvent();
        $msgStart->getEventDefinitions()->push($definition);
        $inner = new Activity();
        $eventSub->addFlowElement($msgStart);
        $eventSub->addFlowElement($inner);
        $msgStart->createFlowTo($inner, $repo);

        $noneStart = new StartEvent();
        $userTask = new Activity();
        $end = new EndEvent();
        $process->addActivity($eventSub)->addActivity($userTask)->addEvent($noneStart)->addEvent($end);
        $noneStart->createFlowTo($userTask, $repo);
        $userTask->createFlowTo($end, $repo);
        $engine->loadProcess($process);

        $instance = $process->call();
        $engine->runToNextState();
        $this->assertGreaterThan(0, $instance->getTokens()->count());

        $thrown = new MessageEventDefinition();
        $thrown->setPayload($message);
        $msgStart->execute($thrown, $instance, null);
        $engine->runToNextState();

        $this->assertEquals(1, $inner->getTokens($instance)->count());
    }

    public function testEventSubProcessStartWithEmptyInstanceReturnsEarly()
    {
        $repo = new Repository();
        $process = new Process();
        $process->setRepository($repo);

        $eventSub = new SubProcess();
        $eventSub->setTriggeredByEvent(true);
        $msgStart = new StartEvent();
        $definition = new MessageEventDefinition();
        $msgStart->getEventDefinitions()->push($definition);
        $eventSub->addFlowElement($msgStart);
        $msgStart->setContainer($eventSub);
        $msgStart->setOwnerProcess($process);
        $msgStart->buildTransitions($repo);

        $empty = new ExecutionInstance();
        $result = $msgStart->execute($definition, $empty, null);
        $this->assertSame($msgStart, $result);
    }

    public function testEventSubProcessStartScansProcessInstancesWhenInstanceNull()
    {
        $repo = new Repository();
        $engine = $this->createEngine($repo);
        $process = new Process();
        $process->setEngine($engine);
        $process->setRepository($repo);

        $message = new Message();
        $message->setId('Msg3');
        $definition = new MessageEventDefinition();
        $definition->setPayload($message);

        $eventSub = new SubProcess();
        $eventSub->setTriggeredByEvent(true);
        $eventSub->setOwnerProcess($process);
        $msgStart = new StartEvent();
        $msgStart->getEventDefinitions()->push($definition);
        $inner = new Activity();
        $eventSub->addFlowElement($msgStart);
        $eventSub->addFlowElement($inner);
        $msgStart->createFlowTo($inner, $repo);

        $noneStart = new StartEvent();
        $userTask = new Activity();
        $end = new EndEvent();
        $process->addActivity($eventSub)->addActivity($userTask)->addEvent($noneStart)->addEvent($end);
        $noneStart->createFlowTo($userTask, $repo);
        $userTask->createFlowTo($end, $repo);
        $engine->loadProcess($process);

        $instance = $process->call();
        $engine->runToNextState();

        $thrown = new MessageEventDefinition();
        $thrown->setPayload($message);
        $msgStart->execute($thrown, null, null);
        $engine->runToNextState();

        $this->assertEquals(1, $inner->getTokens($instance)->count());
    }

    public function testEventSubProcessStartWithNoActiveInstancesReturnsEarly()
    {
        $repo = new Repository();
        $process = new Process();
        $process->setRepository($repo);

        $eventSub = new SubProcess();
        $eventSub->setTriggeredByEvent(true);
        $msgStart = new StartEvent();
        $definition = new MessageEventDefinition();
        $msgStart->getEventDefinitions()->push($definition);
        $eventSub->addFlowElement($msgStart);
        $msgStart->setContainer($eventSub);
        $msgStart->setOwnerProcess($process);
        $msgStart->buildTransitions($repo);

        // Process has an instance with zero tokens.
        $empty = new ExecutionInstance();
        $process->addInstance($empty);

        $result = $msgStart->execute($definition, null, null);
        $this->assertSame($msgStart, $result);
    }

    public function testExecuteWithDataMappingDoesNotCopyPayloadData()
    {
        $repo = new Repository();
        $engine = $this->createEngine($repo);
        $process = new Process();
        $process->setEngine($engine);
        $process->setRepository($repo);

        $message = new Message();
        $message->setId('MsgMap');
        $definition = new MessageEventDefinition();
        $definition->setPayload($message);

        $start = new StartEvent();
        // Non-empty data output associations => skip setData on new store.
        $start->setProperty(
            \ProcessMaker\Nayra\Contracts\Bpmn\CatchEventInterface::BPMN_PROPERTY_DATA_OUTPUT_ASSOCIATION,
            new Collection([$this->createMock(\ProcessMaker\Nayra\Contracts\Bpmn\EntityInterface::class)])
        );
        $start->getEventDefinitions()->push($definition);
        $task = new Activity();
        $end = new EndEvent();
        $process->addActivity($task)->addEvent($start)->addEvent($end);
        $start->createFlowTo($task, $repo);
        $task->createFlowTo($end, $repo);
        $engine->loadProcess($process);

        $thrown = new MessageEventDefinition();
        $thrown->setPayload($message);
        $start->execute($thrown, null, null);
        $engine->runToNextState();

        $this->assertEquals(1, $process->getInstances()->count());
    }

    /**
     * @param Repository $repo
     *
     * @return TestEngine
     */
    private function createEngine(Repository $repo)
    {
        $dispatcher = $this->getMockBuilder(EventBusInterface::class)->getMock();
        $dispatcher->expects($this->any())->method('dispatch');

        return new TestEngine($repo, $dispatcher);
    }
}
