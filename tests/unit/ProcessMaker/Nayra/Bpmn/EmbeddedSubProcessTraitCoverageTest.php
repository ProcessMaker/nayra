<?php

namespace ProcessMaker\Nayra\Bpmn;

use PHPUnit\Framework\TestCase;
use ProcessMaker\Nayra\Bpmn\Models\Activity;
use ProcessMaker\Nayra\Bpmn\Models\AdHocSubProcess;
use ProcessMaker\Nayra\Bpmn\Models\EndEvent;
use ProcessMaker\Nayra\Bpmn\Models\ExclusiveGateway;
use ProcessMaker\Nayra\Bpmn\Models\Process;
use ProcessMaker\Nayra\Bpmn\Models\ScopeExecution;
use ProcessMaker\Nayra\Bpmn\Models\StartEvent;
use ProcessMaker\Nayra\Bpmn\Models\SubProcess;
use ProcessMaker\Nayra\Bpmn\Models\Transaction;
use ProcessMaker\Nayra\Contracts\Bpmn\ActivityInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\AdHocSubProcessInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\FlowElementsContainerInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\TokenInterface;
use ProcessMaker\Test\Models\ExecutionInstance;
use ProcessMaker\Test\Models\Repository;
use ReflectionMethod;

interface ActivityAndFlowContainer extends ActivityInterface, FlowElementsContainerInterface
{
}

/**
 * Cover remaining EmbeddedSubProcessTrait branches.
 */
class EmbeddedSubProcessTraitCoverageTest extends TestCase
{
    public function testBuildInnerTransitionsIsCached()
    {
        $repo = new Repository();
        $sub = new SubProcess();
        $start = new StartEvent();
        $task = new Activity();
        $sub->addFlowElement($start);
        $sub->addFlowElement($task);

        $transitions = [];
        $first = $sub->buildInnerTransitions($repo, $transitions);
        $transitionsAgain = [];
        $second = $sub->buildInnerTransitions($repo, $transitionsAgain);

        $this->assertSame($first, $second);
        $this->assertNotEmpty($transitionsAgain);
    }

    public function testEventTriggeredSubProcessSkipsEnterOnActivation()
    {
        $repo = new Repository();
        $process = new Process();
        $this->wireProcess($process, $repo);

        $eventSub = new SubProcess();
        $eventSub->setId('EventSub');
        $eventSub->setTriggeredByEvent(true);
        $eventSub->setOwnerProcess($process);
        $eventSub->setProcess($process);
        $eventSub->setRepository($repo);

        $inner = new Activity();
        $inner->setId('Inner');
        $eventSub->addFlowElement($inner);

        $token = $this->createMock(TokenInterface::class);
        $token->expects($this->never())->method('setProperty');

        $observers = $eventSub->getObservers();
        $this->assertNotEmpty($observers[ActivityInterface::EVENT_ACTIVITY_ACTIVATED]);
        foreach ($observers[ActivityInterface::EVENT_ACTIVITY_ACTIVATED] as $callback) {
            $callback($eventSub, $token);
        }

        $this->assertEquals(0, $inner->getTokens(new ExecutionInstance())->count());
    }

    public function testAdHocSequentialActivatesOnlyFirstActivity()
    {
        $repo = new Repository();
        $process = new Process();
        $this->wireProcess($process, $repo);

        $adHoc = new AdHocSubProcess();
        $adHoc->setId('AdHoc');
        $adHoc->setOrdering(AdHocSubProcessInterface::ORDERING_SEQUENTIAL);
        $adHoc->setOwnerProcess($process);
        $adHoc->setProcess($process);
        $adHoc->setRepository($repo);

        $nested = new SubProcess();
        $nested->setId('Nested');
        $first = new Activity();
        $first->setId('First');
        $second = new Activity();
        $second->setId('Second');
        // Nested container first so activateAdHocActivity hits the skip branch.
        $adHoc->addFlowElement($nested);
        $adHoc->addFlowElement($first);
        $adHoc->addFlowElement($second);

        $start = new StartEvent();
        $end = new EndEvent();
        $process->addActivity($adHoc)->addEvent($start)->addEvent($end);
        $start->createFlowTo($adHoc, $repo);
        $adHoc->createFlowTo($end, $repo);

        $instance = $process->call();
        $process->getEngine()->runToNextState();

        // Nested FlowElementsContainer is skipped; sequential only tries the first item.
        $this->assertEquals(0, $nested->getTokens($instance)->count());
        $this->assertEquals(0, $first->getTokens($instance)->count());
        $this->assertEquals(0, $second->getTokens($instance)->count());
    }

    public function testAdHocSequentialWithEmptyActivitiesDoesNotFail()
    {
        $repo = new Repository();
        $process = new Process();
        $this->wireProcess($process, $repo);

        $adHoc = new AdHocSubProcess();
        $adHoc->setId('EmptyAdHoc');
        $adHoc->setOrdering(AdHocSubProcessInterface::ORDERING_SEQUENTIAL);
        $adHoc->setOwnerProcess($process);
        $adHoc->setProcess($process);
        $adHoc->setRepository($repo);

        $start = new StartEvent();
        $end = new EndEvent();
        $process->addActivity($adHoc)->addEvent($start)->addEvent($end);
        $start->createFlowTo($adHoc, $repo);
        $adHoc->createFlowTo($end, $repo);

        $instance = $process->call();
        $process->getEngine()->runToNextState();

        $this->assertEquals(1, $adHoc->getTokens($instance)->count());
    }

    public function testCompleteAndCancelGuardsWhenScopeInactive()
    {
        $repo = new Repository();
        $tx = new Transaction();
        $tx->setRepository($repo);
        $token = $this->createMock(TokenInterface::class);
        $instance = new ExecutionInstance();
        $scope = new ScopeExecution($token, $tx, $instance);
        $scope->destroy();

        $complete = new ReflectionMethod(Transaction::class, 'completeEmbeddedSubProcess');
        $complete->setAccessible(true);
        $complete->invoke($tx, $token, $scope);

        $cancel = new ReflectionMethod(Transaction::class, 'cancelTransaction');
        $cancel->setAccessible(true);
        $cancel->invoke($tx, $token, $scope, $instance);

        $this->assertFalse($scope->isActive());
    }

    public function testCancelTransactionBuildsCompensationStatesWhenMissing()
    {
        $repo = new Repository();
        $process = new Process();
        $this->wireProcess($process, $repo);

        $tx = new Transaction();
        $tx->setId('Tx');
        $tx->setOwnerProcess($process);
        $tx->setProcess($process);
        $tx->setRepository($repo);

        $compensate = new Activity();
        $compensate->setId('Compensate');
        $compensate->setProperty('isForCompensation', true);
        $compensate->setRepository($repo);
        $compensate->setOwnerProcess($process);
        $compensate->setProcess($process);
        // Intentionally do not build transitions yet — cancelTransaction should.
        $tx->addFlowElement($compensate);

        $token = $this->createMock(TokenInterface::class);
        $token->expects($this->atLeastOnce())->method('setProperty');
        $token->expects($this->once())->method('setStatus')->with(ActivityInterface::TOKEN_STATE_CLOSED);
        $instance = new ExecutionInstance();
        $instance->setProcess($process);
        $instance->setDataStore($repo->createDataStore());
        $token->method('getInstance')->willReturn($instance);
        $scope = new ScopeExecution($token, $tx, $instance);

        $cancel = new ReflectionMethod(Transaction::class, 'cancelTransaction');
        $cancel->setAccessible(true);
        $cancel->invoke($tx, $token, $scope, $instance);

        $this->assertFalse($scope->isActive());
        $this->assertTrue($scope->isCancelled());
        $this->assertNotEmpty($compensate->getStates());
        $this->assertEquals(1, $compensate->getTokens($instance)->count());
    }

    public function testContainsNodeCoversNestedAndContainerWalk()
    {
        $outer = new SubProcess();
        $inner = new SubProcess();
        $innerActivity = new Activity();
        $innerEvent = new StartEvent();
        $innerGateway = new ExclusiveGateway();
        $outerGateway = new ExclusiveGateway();

        $inner->addFlowElement($innerActivity);
        $inner->addFlowElement($innerEvent);
        $inner->addFlowElement($innerGateway);
        $outer->addFlowElement($inner);
        $outer->addFlowElement($outerGateway);

        $method = new ReflectionMethod(SubProcess::class, 'containsNode');
        $method->setAccessible(true);

        $this->assertTrue($method->invoke($outer, $inner));
        $this->assertTrue($method->invoke($outer, $innerActivity));
        $this->assertTrue($method->invoke($outer, $innerEvent));
        $this->assertTrue($method->invoke($outer, $innerGateway));
        $this->assertTrue($method->invoke($outer, $outerGateway));

        // Nested SubProcess has containsNode; recursive call path.
        $deepEvent = new EndEvent();
        $inner->addFlowElement($deepEvent);
        $this->assertTrue($method->invoke($outer, $deepEvent));

        // Container without containsNode — scan nested getEvents/Activities/Gateways.
        $nestedEvent = new EndEvent();
        $nestedActivity = new Activity();
        $nestedGateway = new ExclusiveGateway();
        $plainContainer = $this->createMock(ActivityAndFlowContainer::class);
        $plainContainer->method('getEvents')->willReturn(new Collection([$nestedEvent]));
        $plainContainer->method('getActivities')->willReturn(new Collection([$nestedActivity]));
        $plainContainer->method('getGateways')->willReturn(new Collection([$nestedGateway]));
        $outer->getActivities()->push($plainContainer);
        $this->assertTrue($method->invoke($outer, $nestedEvent));
        $this->assertTrue($method->invoke($outer, $nestedActivity));
        $this->assertTrue($method->invoke($outer, $nestedGateway));

        // Walk via getContainer when not listed directly under a non-containsNode container.
        $orphan = new Activity();
        $orphan->setContainer($outer);
        $this->assertTrue($method->invoke($outer, $orphan));

        $stranger = new Activity();
        $this->assertFalse($method->invoke($outer, $stranger));

        $noContainer = new \stdClass();
        $this->assertFalse($method->invoke($outer, $noContainer));
    }

    public function testCheckIgnoresInactiveCancelledAndNonActiveTokens()
    {
        $repo = new Repository();
        $process = new Process();
        $engine = $this->wireProcess($process, $repo);

        $sub = new SubProcess();
        $sub->setId('Sub');
        $sub->setOwnerProcess($process);
        $sub->setProcess($process);
        $sub->setRepository($repo);
        $startInner = new StartEvent();
        $task = new Activity();
        $endInner = new EndEvent();
        $sub->addFlowElement($startInner);
        $sub->addFlowElement($task);
        $sub->addFlowElement($endInner);
        $startInner->createFlowTo($task, $repo);
        $task->createFlowTo($endInner, $repo);

        $start = new StartEvent();
        $end = new EndEvent();
        $process->addActivity($sub)->addEvent($start)->addEvent($end);
        $start->createFlowTo($sub, $repo);
        $sub->createFlowTo($end, $repo);

        $instance = $process->call();
        $engine->runToNextState();
        $token = $sub->getTokens($instance)->item(0);
        $scope = $token->getProperty('subprocessScope');

        $check = new ReflectionMethod(SubProcess::class, 'checkEmbeddedSubProcessCompletion');
        $check->setAccessible(true);

        // Non-ACTIVE parent token: early return, scope stays active.
        $token->setStatus(ActivityInterface::TOKEN_STATE_CLOSED);
        $check->invoke($sub, $token, $scope, $instance, false);
        $this->assertTrue($scope->isActive());

        // Inactive / cancelled scope: early return.
        $token->setStatus(ActivityInterface::TOKEN_STATE_ACTIVE);
        $scope->markCancelled();
        $check->invoke($sub, $token, $scope, $instance, false);
        $this->assertTrue($scope->isCancelled());
        $this->assertEquals(ActivityInterface::TOKEN_STATE_ACTIVE, $token->getStatus());
    }

    public function testCheckCompletesAdHocWhenCompletionConditionMet()
    {
        $repo = new Repository();
        $process = new Process();
        $this->wireProcess($process, $repo);

        $adHoc = new AdHocSubProcess();
        $adHoc->setOwnerProcess($process);
        $adHoc->setProcess($process);
        $adHoc->setRepository($repo);
        $condition = $this->createMock(\ProcessMaker\Nayra\Contracts\Bpmn\FormalExpressionInterface::class);
        $condition->method('__invoke')->willReturn(true);
        $adHoc->setCompletionCondition($condition);

        $token = $this->createMock(TokenInterface::class);
        $token->method('getStatus')->willReturn(ActivityInterface::TOKEN_STATE_ACTIVE);
        $token->expects($this->once())->method('setStatus')->with(ActivityInterface::TOKEN_STATE_COMPLETED);
        $instance = new ExecutionInstance();
        $instance->setDataStore($repo->createDataStore()->setData([]));
        $scope = new ScopeExecution($token, $adHoc, $instance);

        $check = new ReflectionMethod(AdHocSubProcess::class, 'checkEmbeddedSubProcessCompletion');
        $check->setAccessible(true);
        $check->invoke($adHoc, $token, $scope, $instance, true);

        $this->assertFalse($scope->isActive());
    }

    /**
     * @param Repository $repo
     *
     * @return \ProcessMaker\Bpmn\TestEngine
     */
    private function createEngine(Repository $repo)
    {
        $dispatcher = $this->getMockBuilder(\ProcessMaker\Nayra\Contracts\EventBusInterface::class)->getMock();
        $dispatcher->expects($this->any())->method('dispatch');
        $engine = new \ProcessMaker\Bpmn\TestEngine($repo, $dispatcher);

        return $engine;
    }

    /**
     * @param Process $process
     * @param Repository $repo
     *
     * @return \ProcessMaker\Bpmn\TestEngine
     */
    private function wireProcess(Process $process, Repository $repo)
    {
        $engine = $this->createEngine($repo);
        $process->setEngine($engine);
        $process->setRepository($repo);
        $process->setDispatcher($engine->getDispatcher());

        return $engine;
    }
}
