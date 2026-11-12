<?php

namespace ProcessMaker\Nayra\Bpmn\Models;

use PHPUnit\Framework\TestCase;
use ProcessMaker\Nayra\Contracts\Bpmn\CatchEventInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\EventDefinitionInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\FlowNodeInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\TokenInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\DataStoreInterface;
use ProcessMaker\Nayra\Contracts\Engine\EngineInterface;
use ProcessMaker\Nayra\Contracts\Engine\EventDefinitionBusInterface;
use ProcessMaker\Nayra\Contracts\Engine\ExecutionInstanceInterface;

/**
 * Full coverage for CancelEventDefinition.
 */
class CancelEventDefinitionTest extends TestCase
{
    public function testAssertsRuleAcceptsCancelEventDefinition()
    {
        $definition = new CancelEventDefinition();
        $target = $this->createMock(FlowNodeInterface::class);

        $this->assertTrue(
            $definition->assertsRule(new CancelEventDefinition(), $target, null, null)
        );
    }

    public function testAssertsRuleRejectsOtherEventDefinition()
    {
        $definition = new CancelEventDefinition();
        $other = $this->createMock(EventDefinitionInterface::class);
        $target = $this->createMock(FlowNodeInterface::class);

        $this->assertFalse($definition->assertsRule($other, $target));
    }

    public function testExecuteReturnsSelf()
    {
        $definition = new CancelEventDefinition();
        $event = $this->createMock(EventDefinitionInterface::class);
        $target = $this->createMock(FlowNodeInterface::class);
        $instance = $this->createMock(ExecutionInstanceInterface::class);
        $token = $this->createMock(TokenInterface::class);

        $this->assertSame($definition, $definition->execute($event, $target, $instance, $token));
    }

    public function testTraitHelpersUsedByCancelEventDefinition()
    {
        $definition = new CancelEventDefinition();
        $engine = $this->createMock(EngineInterface::class);
        $bus = $this->createMock(EventDefinitionBusInterface::class);
        $catch = $this->createMock(CatchEventInterface::class);

        $engine->method('getEventDefinitionBus')->willReturn($bus);
        $bus->expects($this->once())
            ->method('registerCatchEvent')
            ->with($catch, $definition, $this->isType('callable'));

        $definition->registerWithCatchEvent($engine, $catch);
        $definition->catchEventActivated($engine, $catch, null);

        $this->assertTrue($definition->shouldCatchEventDefinition($this->createMock(EventDefinitionInterface::class)));
        $this->assertSame([], $definition->getPayloadData(null, null));
        $this->assertNull($definition->getPayload());

        $definition->setDoNotTriggerStartEvents(true);
        $this->assertTrue($definition->getDoNotTriggerStartEvents());

        $instance = $this->createMock(ExecutionInstanceInterface::class);
        $dataStore = $this->createMock(DataStoreInterface::class);
        $dataStore->method('getData')->willReturn(['a' => 1]);
        $instance->method('getDataStore')->willReturn($dataStore);
        $token = $this->createMock(TokenInterface::class);
        $token->method('getInstance')->willReturn($instance);
        $this->assertSame(['a' => 1], $definition->getPayloadData($token, $catch));
    }
}
