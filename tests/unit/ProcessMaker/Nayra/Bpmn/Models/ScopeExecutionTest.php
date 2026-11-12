<?php

namespace ProcessMaker\Nayra\Bpmn\Models;

use PHPUnit\Framework\TestCase;
use ProcessMaker\Nayra\Bpmn\BaseTrait;
use ProcessMaker\Nayra\Bpmn\FlowElementsContainerTrait;
use ProcessMaker\Nayra\Contracts\Bpmn\FlowElementsContainerInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\TokenInterface;
use ProcessMaker\Test\Models\ExecutionInstance;
use ProcessMaker\Test\Models\Repository;

/**
 * Full coverage for ScopeExecution.
 */
class ScopeExecutionTest extends TestCase
{
    public function testAccessorsAndLifecycle()
    {
        $token = $this->createMock(TokenInterface::class);
        $token->expects($this->once())
            ->method('setProperty')
            ->with('subprocessScope', null);

        $container = new SubProcess();
        $instance = new ExecutionInstance();
        $scope = new ScopeExecution($token, $container, $instance);

        $this->assertNotSame('', $scope->getId());
        $this->assertStringStartsWith('scope_', $scope->getId());
        $this->assertSame($token, $scope->getParentToken());
        $this->assertSame($container, $scope->getContainer());
        $this->assertSame($instance, $scope->getInstance());
        $this->assertTrue($scope->isActive());
        $this->assertFalse($scope->isCancelled());

        $scope->markCancelled();
        $this->assertTrue($scope->isCancelled());

        $scope->destroy();
        $this->assertFalse($scope->isActive());
    }

    public function testGetParentScopeWhenContainerHasNoGetContainer()
    {
        $token = $this->createMock(TokenInterface::class);
        $container = new class implements FlowElementsContainerInterface {
            use BaseTrait;
            use FlowElementsContainerTrait;
        };
        $scope = new ScopeExecution($token, $container, new ExecutionInstance());

        $this->assertNull($scope->getParentScope());
    }

    public function testGetParentScopeWhenParentContainerIsNotFlowElementsContainer()
    {
        $token = $this->createMock(TokenInterface::class);
        $container = new SubProcess();
        $container->setContainer(new \stdClass());
        $scope = new ScopeExecution($token, $container, new ExecutionInstance());

        $this->assertNull($scope->getParentScope());
    }

    public function testGetParentScopeWhenParentContainerHasNoGetTokens()
    {
        $token = $this->createMock(TokenInterface::class);
        $parent = new class implements FlowElementsContainerInterface {
            use BaseTrait;
            use FlowElementsContainerTrait;
        };
        $container = new SubProcess();
        $container->setContainer($parent);
        $scope = new ScopeExecution($token, $container, new ExecutionInstance());

        $this->assertNull($scope->getParentScope());
    }

    public function testGetParentScopeWhenParentHasNoTokens()
    {
        $token = $this->createMock(TokenInterface::class);
        $instance = new ExecutionInstance();
        $outer = new SubProcess();
        $inner = new SubProcess();
        $inner->setContainer($outer);
        // Outer SubProcess has getTokens but no tokens for this instance.
        $scope = new ScopeExecution($token, $inner, $instance);

        $this->assertNull($scope->getParentScope());
    }

    public function testGetParentScopeWhenTokenPropertyIsNotScopeExecution()
    {
        $instance = new ExecutionInstance();
        $outer = new SubProcess();
        $inner = new SubProcess();
        $inner->setContainer($outer);

        $parentToken = $this->createMock(TokenInterface::class);
        $parentToken->method('getProperty')
            ->with('subprocessScope')
            ->willReturn('not-a-scope');
        $parentToken->method('getInstance')->willReturn($instance);
        $parentToken->method('getStatus')->willReturn('ACTIVE');
        $parentToken->method('getIndex')->willReturn(0);

        // Put a token on the outer subprocess active state.
        $outer->buildTransitions(new Repository());
        foreach ($outer->getStates() as $state) {
            if ($state->getName() === 'ACTIVE') {
                $state->addToken($instance, $parentToken, true);
                break;
            }
        }

        $childToken = $this->createMock(TokenInterface::class);
        $scope = new ScopeExecution($childToken, $inner, $instance);

        $this->assertNull($scope->getParentScope());
    }

    public function testGetParentScopeReturnsParentScopeExecution()
    {
        $instance = new ExecutionInstance();
        $outer = new SubProcess();
        $inner = new SubProcess();
        $inner->setContainer($outer);

        $repo = new Repository();
        $outer->buildTransitions($repo);

        $outerParentToken = $this->createMock(TokenInterface::class);
        $outerScope = new ScopeExecution($outerParentToken, $outer, $instance);
        $outerParentToken->method('getProperty')
            ->with('subprocessScope')
            ->willReturn($outerScope);
        $outerParentToken->method('getInstance')->willReturn($instance);
        $outerParentToken->method('getStatus')->willReturn('ACTIVE');
        $outerParentToken->method('getIndex')->willReturn(0);

        foreach ($outer->getStates() as $state) {
            if ($state->getName() === 'ACTIVE') {
                $state->addToken($instance, $outerParentToken, true);
                break;
            }
        }

        $innerToken = $this->createMock(TokenInterface::class);
        $innerScope = new ScopeExecution($innerToken, $inner, $instance);

        $this->assertSame($outerScope, $innerScope->getParentScope());
    }

    public function testGetActiveTokensCountIncludesNestedNodesAndSkipsSelf()
    {
        $instance = new ExecutionInstance();
        $repo = new Repository();

        $outer = new SubProcess();
        $inner = new SubProcess();
        $deep = new SubProcess();
        $activity = new Activity();
        $event = new StartEvent();
        $innerActivity = new Activity();
        $deepActivity = new Activity();

        $outer->addFlowElement($activity);
        $outer->addFlowElement($event);
        $outer->addFlowElement($inner);
        // Force the skip branch: container listed among its own activities.
        $outer->getActivities()->push($outer);

        $innerGateway = new ParallelGateway();
        $outerGateway = new ExclusiveGateway();
        $inner->addFlowElement($innerActivity);
        $inner->addFlowElement($innerGateway);
        $inner->addFlowElement($deep);
        $deep->addFlowElement($deepActivity);
        $outer->addFlowElement($outerGateway);

        foreach ([$activity, $event, $inner, $innerActivity, $innerGateway, $deep, $deepActivity, $outer, $outerGateway] as $node) {
            $node->setRepository($repo);
            $node->buildTransitions($repo);
        }
        // Gateways create token places only when an input is connected.
        $innerGateway->getInputPlace();
        $outerGateway->getInputPlace();

        $token = $this->createMock(TokenInterface::class);
        $scope = new ScopeExecution($token, $outer, $instance);

        foreach ([$activity, $event, $innerActivity, $deepActivity, $innerGateway, $outerGateway] as $node) {
            $states = $node->getStates();
            $this->assertNotEmpty($states, get_class($node) . ' should have states');
            $nodeToken = $this->createMock(TokenInterface::class);
            $nodeToken->method('getInstance')->willReturn($instance);
            $nodeToken->method('getStatus')->willReturn($states[0]->getName());
            $nodeToken->method('getIndex')->willReturn(0);
            $states[0]->addToken($instance, $nodeToken, true);
        }

        // Also put a token on outer itself: counted in getInnerFlowNodes then skipped.
        foreach ($outer->getStates() as $state) {
            if ($state->getName() === 'ACTIVE') {
                $selfToken = $this->createMock(TokenInterface::class);
                $selfToken->method('getInstance')->willReturn($instance);
                $selfToken->method('getStatus')->willReturn('ACTIVE');
                $selfToken->method('getIndex')->willReturn(0);
                $state->addToken($instance, $selfToken, true);
                break;
            }
        }

        // activity + event + outerGateway + innerActivity + innerGateway + deepActivity;
        // outer self-token skipped.
        $this->assertSame(6, $scope->getActiveTokensCount());
    }
}
