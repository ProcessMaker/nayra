<?php

namespace ProcessMaker\Nayra\Bpmn;

use PHPUnit\Framework\TestCase;
use ProcessMaker\Nayra\Bpmn\Models\DataStore;
use ProcessMaker\Nayra\Bpmn\Models\ExclusiveGateway;
use ProcessMaker\Nayra\Bpmn\Models\Process;
use ProcessMaker\Nayra\Contracts\Engine\EngineInterface;

/**
 * Tests for the ConditionedTransition class
 */
class ConditionedExclusiveTransitionTest extends TestCase
{
    /**
     * Tests that setters and getters are working properly
     */
    public function testAssertCondition()
    {
        $gateway = new ExclusiveGateway();
        $conditionedTransition = new ConditionedExclusiveTransition($gateway);
        $conditionedTransition->setCondition(function ($data) {
            return $data['foo'];
        });

        // Mock a engine data store
        $process = new Process();
        $engine = $this->getMockBuilder(EngineInterface::class)->getMock();
        $dataStore = new DataStore();
        $dataStore->setData(['foo' => 'bar']);
        $engine->method('getDataStore')->willReturn($dataStore);
        $process->setEngine($engine);
        $conditionedTransition->setOwnerProcess($process);

        // Assertion: The condition should return 'bar'
        $this->assertEquals('bar', $conditionedTransition->assertCondition());
    }
}
