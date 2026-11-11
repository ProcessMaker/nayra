<?php

namespace Tests\Feature\Engine;

use ProcessMaker\Nayra\Contracts\Bpmn\ActivityInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\AdHocSubProcessInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\SubProcessInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\TransactionInterface;
use ProcessMaker\Nayra\Storage\BpmnDocument;

/**
 * SubProcess import, execution, export, and validation tests.
 */
class SubProcessTest extends EngineTestCase
{
    private function load(string $file): BpmnDocument
    {
        $bpmn = new BpmnDocument();
        $bpmn->setEngine($this->engine);
        $bpmn->setFactory($this->repository);
        $bpmn->load(__DIR__ . '/files/' . $file);
        $this->engine->loadBpmnDocument($bpmn);

        return $bpmn;
    }

    public function testImportEmbeddedSubProcess()
    {
        $bpmn = $this->load('SubProcess_Embedded.bpmn');
        $process = $bpmn->getProcess('Process_1');
        $sub = $bpmn->getSubProcess('SubProcess_1');

        $this->assertInstanceOf(SubProcessInterface::class, $sub);
        $this->assertEquals('SubProcess_1', $sub->getId());
        $this->assertEquals('Review', $sub->getName());
        $this->assertFalse($sub->getTriggeredByEvent());
        $this->assertContains($sub, $process->getActivities()->toArray());
    }

    public function testNestedNodesBelongToSubProcess()
    {
        $bpmn = $this->load('SubProcess_Embedded.bpmn');
        $sub = $bpmn->getSubProcess('SubProcess_1');
        $start = $bpmn->getStartEvent('Start_1');
        $task = $bpmn->getActivity('Task_1');
        $end = $bpmn->getEndEvent('End_1');

        $this->assertSame($sub, $start->getContainer());
        $this->assertSame($sub, $task->getContainer());
        $this->assertSame($sub, $end->getContainer());
        $this->assertContains($start, $sub->getEvents()->toArray());
        $this->assertContains($task, $sub->getActivities()->toArray());
        $this->assertContains($end, $sub->getEvents()->toArray());
        $this->assertGreaterThanOrEqual(1, $sub->getArtifacts()->count());
        $this->assertGreaterThanOrEqual(1, $sub->getLaneSets()->count());
        $this->assertSame($bpmn->getProcess('Process_1'), $task->getOwnerProcess());
    }

    public function testValidationRules()
    {
        $bpmn = $this->load('SubProcess_Embedded.bpmn');
        $sub = $bpmn->getSubProcess('SubProcess_1');
        $this->assertSame([], $sub->validate());

        $invalid = $this->repository->createSubProcess();
        $invalid->setId(null);
        $errors = $invalid->validate();
        $this->assertNotEmpty($errors);

        $withId = $this->repository->createSubProcess();
        $withId->setId('SubProcess_X');
        $this->assertNotEmpty($withId->validate());
    }

    public function testExecuteSimpleSubProcess()
    {
        $bpmn = $this->load('SubProcess_Embedded.bpmn');
        $process = $bpmn->getProcess('Process_1');
        $start = $bpmn->getStartEvent('Start_Parent');
        $taskA = $bpmn->getActivity('Task_A');
        $sub = $bpmn->getSubProcess('SubProcess_1');
        $review = $bpmn->getActivity('Task_1');
        $taskB = $bpmn->getActivity('Task_B');

        $instance = $process->call();
        $this->engine->runToNextState();

        $this->assertEquals(1, $taskA->getTokens($instance)->count());
        $tokenA = $taskA->getTokens($instance)->item(0);
        $taskA->complete($tokenA);
        $this->engine->runToNextState();

        $this->assertEquals(1, $process->getInstances()->count());
        $this->assertEquals(1, $sub->getTokens($instance)->count());
        $this->assertTrue((bool) $sub->getTokens($instance)->item(0)->getProperty('subprocessWaiting'));
        $this->assertInstanceOf(
            \ProcessMaker\Nayra\Bpmn\Models\ScopeExecution::class,
            $sub->getTokens($instance)->item(0)->getProperty('subprocessScope')
        );
        $this->assertEquals(1, $review->getTokens($instance)->count());
        $this->assertEquals(0, $taskB->getTokens($instance)->count());
        $this->assertEquals(ActivityInterface::TOKEN_STATE_ACTIVE, $sub->getTokens($instance)->item(0)->getStatus());

        $review->complete($review->getTokens($instance)->item(0));
        $this->engine->runToNextState();

        $this->assertEquals(0, $review->getTokens($instance)->count());
        $this->assertEquals(1, $taskB->getTokens($instance)->count());
        $this->assertEquals(1, $process->getInstances()->count());
    }

    public function testExecuteNestedSubProcess()
    {
        $bpmn = $this->load('SubProcess_Nested.bpmn');
        $process = $bpmn->getProcess('Process_Nested');
        $outer = $bpmn->getSubProcess('Outer');
        $inner = $bpmn->getSubProcess('Inner');
        $review = $bpmn->getActivity('Review');
        $taskB = $bpmn->getActivity('Task_B');

        $instance = $process->call();
        $this->engine->runToNextState();

        $this->assertEquals(1, $review->getTokens($instance)->count());
        $this->assertEquals(1, $outer->getTokens($instance)->count());
        $this->assertEquals(1, $inner->getTokens($instance)->count());
        $this->assertSame($outer, $inner->getContainer());
        $outerScope = $outer->getTokens($instance)->item(0)->getProperty('subprocessScope');
        $innerScope = $inner->getTokens($instance)->item(0)->getProperty('subprocessScope');
        $this->assertSame($outerScope, $innerScope->getParentScope());
        $this->assertEquals(0, $taskB->getTokens($instance)->count());

        $review->complete($review->getTokens($instance)->item(0));
        $this->engine->runToNextState();

        $this->assertEquals(1, $taskB->getTokens($instance)->count());
        $this->assertFalse($outerScope->isActive());
        $this->assertFalse($innerScope->isActive());
    }

    public function testExecuteParallelPathsInsideSubProcess()
    {
        $bpmn = $this->load('SubProcess_Parallel.bpmn');
        $process = $bpmn->getProcess('Process_Parallel');
        $left = $bpmn->getActivity('Left');
        $right = $bpmn->getActivity('Right');

        $instance = $process->call();
        $this->engine->runToNextState();

        $this->assertEquals(1, $left->getTokens($instance)->count());
        $this->assertEquals(1, $right->getTokens($instance)->count());

        $left->complete($left->getTokens($instance)->item(0));
        $this->engine->runToNextState();
        $right->complete($right->getTokens($instance)->item(0));
        $this->engine->runToNextState();

        $this->assertEquals(0, $process->getInstances()->item(0)->getTokens()->count());
    }

    public function testExecuteGatewayInsideSubProcess()
    {
        $bpmn = $this->load('SubProcess_Gateway.bpmn');
        $process = $bpmn->getProcess('Process_Gateway');
        $cond = $bpmn->getActivity('CondTask');
        $default = $bpmn->getActivity('DefaultTask');

        $data = $this->repository->createDataStore();
        $data->setData(['takeCond' => true]);
        $instance = $this->engine->createExecutionInstance($process, $data);
        $bpmn->getStartEvent('Start_Parent')->start($instance);
        $this->engine->runToNextState();

        $this->assertEquals(1, $cond->getTokens($instance)->count());
        $this->assertEquals(0, $default->getTokens($instance)->count());

        $bpmn2 = $this->load('SubProcess_Gateway.bpmn');
        $process2 = $bpmn2->getProcess('Process_Gateway');
        $cond2 = $bpmn2->getActivity('CondTask');
        $default2 = $bpmn2->getActivity('DefaultTask');
        $data2 = $this->repository->createDataStore();
        $data2->setData(['takeCond' => false]);
        $instance2 = $this->engine->createExecutionInstance($process2, $data2);
        $bpmn2->getStartEvent('Start_Parent')->start($instance2);
        $this->engine->runToNextState();

        $this->assertEquals(0, $cond2->getTokens($instance2)->count());
        $this->assertEquals(1, $default2->getTokens($instance2)->count());
    }

    public function testImportTransactionAndAdHoc()
    {
        $txDoc = $this->load('SubProcess_Transaction.bpmn');
        $tx = $txDoc->getElementInstanceById('Transaction_1');
        $this->assertInstanceOf(TransactionInterface::class, $tx);
        $this->assertNotInstanceOf(AdHocSubProcessInterface::class, $tx);

        $adDoc = $this->load('SubProcess_AdHoc.bpmn');
        $ad = $adDoc->getElementInstanceById('AdHoc_1');
        $this->assertInstanceOf(AdHocSubProcessInterface::class, $ad);
        $this->assertNotInstanceOf(TransactionInterface::class, $ad);
    }

    public function testRoundTripXmlConsistency()
    {
        $bpmn = $this->load('SubProcess_Embedded.bpmn');
        $xml = $bpmn->saveXML();
        $this->assertStringContainsString('bpmn:subProcess', $xml);
        $this->assertStringContainsString('id="SubProcess_1"', $xml);
        $this->assertStringContainsString('name="Review"', $xml);
        $this->assertStringContainsString('Start_1', $xml);
        $this->assertStringContainsString('Task_1', $xml);
        $this->assertStringContainsString('Annotation_1', $xml);
        $this->assertStringContainsString('LaneSet_1', $xml);
        $this->assertStringContainsString(BpmnDocument::BPMN_MODEL, $xml);

        $reload = new BpmnDocument();
        $reload->setEngine($this->engine);
        $reload->setFactory($this->repository);
        $reload->loadXML($xml);
        $sub = $reload->getSubProcess('SubProcess_1');
        $this->assertEquals('Review', $sub->getName());
        $this->assertFalse($sub->getTriggeredByEvent());
        $this->assertSame($sub, $reload->getActivity('Task_1')->getContainer());

        $txDoc = $this->load('SubProcess_Transaction.bpmn');
        $txXml = $txDoc->saveXML();
        $this->assertStringContainsString('bpmn:transaction', $txXml);
        $adDoc = $this->load('SubProcess_AdHoc.bpmn');
        $adXml = $adDoc->saveXML();
        $this->assertStringContainsString('bpmn:adHocSubProcess', $adXml);
    }

    public function testProcessWithoutSubProcessUnchanged()
    {
        $process = $this->repository->createProcess();
        $process->setEngine($this->engine);
        $start = $this->repository->createStartEvent();
        $task = $this->repository->createActivity();
        $end = $this->repository->createEndEvent();
        $process->addActivity($task)->addEvent($start)->addEvent($end);
        $start->createFlowTo($task, $this->repository);
        $task->createFlowTo($end, $this->repository);

        $instance = $process->call();
        $this->engine->runToNextState();
        $this->assertEquals(1, $task->getTokens($instance)->count());
        $task->complete($task->getTokens($instance)->item(0));
        $this->engine->runToNextState();
        $this->assertEquals(0, $instance->getTokens()->count());
        $this->assertEquals(1, $process->getInstances()->count());
    }

    public function testRestoreActiveSubProcessScope()
    {
        $bpmn = $this->load('SubProcess_Embedded.bpmn');
        $process = $bpmn->getProcess('Process_1');
        $taskA = $bpmn->getActivity('Task_A');
        $sub = $bpmn->getSubProcess('SubProcess_1');
        $review = $bpmn->getActivity('Task_1');

        $instance = $process->call();
        $this->engine->runToNextState();
        $taskA->complete($taskA->getTokens($instance)->item(0));
        $this->engine->runToNextState();

        $parentToken = $sub->getTokens($instance)->item(0);
        $scope = $parentToken->getProperty('subprocessScope');
        $this->assertNotNull($scope);
        $this->assertSame($instance, $scope->getInstance());
        $this->assertSame($sub, $scope->getContainer());
        $this->assertSame($parentToken, $scope->getParentToken());
        $this->assertEquals(1, $review->getTokens($instance)->count());
        $this->assertGreaterThan(0, $scope->getActiveTokensCount());
    }

    public function testEventSubProcessStartsFromEvent()
    {
        $bpmn = $this->load('SubProcess_Event.bpmn');
        $order = $bpmn->getProcess('OrderProcess');
        $sender = $bpmn->getProcess('SenderProcess');
        $userTask = $bpmn->getActivity('User_Task');
        $eventInner = $bpmn->getActivity('Event_Inner_Task');
        $messageStart = $bpmn->getStartEvent('MessageStart_EventSub');
        $eventSub = $bpmn->getSubProcess('Event_Sub');

        $this->assertTrue($eventSub->getTriggeredByEvent());
        $this->assertSame([], array_filter($eventSub->validate(), function ($e) {
            return strpos($e, 'requires at least one start event') !== false;
        }));

        $orderInstance = $order->call();
        $this->engine->runToNextState();
        $this->assertEquals(1, $userTask->getTokens($orderInstance)->count());
        $this->assertEquals(0, $eventInner->getTokens($orderInstance)->count());
        $this->assertTrue($messageStart->isSubscribedForInstance($orderInstance));

        $sender->call();
        $this->engine->runToNextState();

        $this->assertEquals(1, $eventInner->getTokens($orderInstance)->count());
        $this->assertEquals(1, $userTask->getTokens($orderInstance)->count());

        $eventInner->complete($eventInner->getTokens($orderInstance)->item(0));
        $this->engine->runToNextState();
        $userTask->complete($userTask->getTokens($orderInstance)->item(0));
        $this->engine->runToNextState();
        $this->assertEquals(0, $orderInstance->getTokens()->count());
        $this->assertFalse($messageStart->isSubscribedForInstance($orderInstance));
    }

    public function testTransactionCompletesAndCancelPath()
    {
        $bpmn = $this->load('SubProcess_Transaction.bpmn');
        $process = $bpmn->getProcess('Process_Transaction');
        $work = $bpmn->getActivity('Work');
        $after = $bpmn->getActivity('After');
        $compensate = $bpmn->getActivity('Compensate');

        $instance = $process->call();
        $this->engine->runToNextState();
        $work->complete($work->getTokens($instance)->item(0));
        $this->engine->runToNextState();
        $this->assertEquals(1, $after->getTokens($instance)->count());
        $this->assertEquals(0, $compensate->getTokens($instance)->count());
    }

    public function testTransactionCancelActivatesCompensation()
    {
        $process = $this->repository->createProcess();
        $process->setEngine($this->engine);
        $start = $this->repository->createStartEvent();
        $tx = $this->repository->createTransaction();
        $tx->setId('Transaction_1');
        $after = $this->repository->createActivity();
        $after->setId('After');
        $end = $this->repository->createEndEvent();

        $txStart = $this->repository->createStartEvent();
        $work = $this->repository->createActivity();
        $work->setId('Work');
        $compensate = $this->repository->createActivity();
        $compensate->setId('Compensate');
        $compensate->setProperty('isForCompensation', true);
        $cancelEnd = $this->repository->createEndEvent();
        $cancelEnd->getEventDefinitions()->push($this->repository->createCancelEventDefinition());

        $tx->addFlowElement($txStart);
        $tx->addFlowElement($work);
        $tx->addFlowElement($compensate);
        $tx->addFlowElement($cancelEnd);
        $txStart->setContainer($tx);
        $work->setContainer($tx);
        $compensate->setContainer($tx);
        $cancelEnd->setContainer($tx);
        $txStart->setOwnerProcess($process);
        $work->setOwnerProcess($process);
        $compensate->setOwnerProcess($process);
        $cancelEnd->setOwnerProcess($process);
        $txStart->setProcess($process);
        $work->setProcess($process);
        $compensate->setProcess($process);
        $cancelEnd->setProcess($process);
        $txStart->createFlowTo($work, $this->repository);
        $work->createFlowTo($cancelEnd, $this->repository);

        $process->addActivity($tx)->addActivity($after)->addEvent($start)->addEvent($end);
        $start->createFlowTo($tx, $this->repository);
        $tx->createFlowTo($after, $this->repository);
        $after->createFlowTo($end, $this->repository);

        $instance = $process->call();
        $this->engine->runToNextState();
        $this->assertEquals(1, $work->getTokens($instance)->count());
        $work->complete($work->getTokens($instance)->item(0));
        $this->engine->runToNextState();

        $this->assertEquals(1, $compensate->getTokens($instance)->count());
        $this->assertEquals(0, $after->getTokens($instance)->count());
    }

    public function testAdHocNonSequentialAndCompletionCondition()
    {
        $bpmn = $this->load('SubProcess_AdHoc.bpmn');
        $process = $bpmn->getProcess('Process_AdHoc');
        $left = $bpmn->getActivity('Left');
        $right = $bpmn->getActivity('Right');
        $after = $bpmn->getActivity('After');
        $adHoc = $bpmn->getElementInstanceById('AdHoc_1');

        $data = $this->repository->createDataStore();
        $data->setData(['done' => false]);
        $instance = $this->engine->createExecutionInstance($process, $data);
        $bpmn->getStartEvent('Start_Parent')->start($instance);
        $this->engine->runToNextState();

        $this->assertEquals(1, $left->getTokens($instance)->count());
        $this->assertEquals(1, $right->getTokens($instance)->count());
        $this->assertEquals(0, $after->getTokens($instance)->count());

        $instance->getDataStore()->setData(['done' => true]);
        $left->complete($left->getTokens($instance)->item(0));
        $this->engine->runToNextState();

        $this->assertEquals(1, $after->getTokens($instance)->count());
        $this->assertInstanceOf(AdHocSubProcessInterface::class, $adHoc);
    }
}
