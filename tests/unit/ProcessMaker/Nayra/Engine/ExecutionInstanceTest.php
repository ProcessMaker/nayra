<?php

namespace ProcessMaker\Nayra\Engine;

use PHPUnit\Framework\TestCase;
use ProcessMaker\Bpmn\TestEngine;
use ProcessMaker\Nayra\Contracts\Bpmn\DataStoreInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\ProcessInterface;
use ProcessMaker\Nayra\Contracts\EventBusInterface;
use ProcessMaker\Test\Models\Repository;

/**
 * Execution Instance tests
 */
class ExecutionInstanceTest extends TestCase
{
    /**
     * Tests that a new ExecutionInstance is initialized correctly
     */
    public function testExecutionInstanceInitialization()
    {
        // mocks to be used in the ExecutionInstance constructor
        $mockDispatcher = $this->getMockBuilder(EventBusInterface::class)
            ->getMock();

        $engine = new TestEngine(new Repository(), $mockDispatcher);
        $mockProcess = $this->getMockForAbstractClass(ProcessInterface::class);
        $mockStore = $this->getMockForAbstractClass(DataStoreInterface::class);

        $instance = new ExecutionInstance();
        $instance->linkTo($engine, $mockProcess, $mockStore);

        //Assertion: the getProcess method should return the same injected process
        $this->assertEquals($mockProcess, $instance->getProcess());

        //Assertion: the getDataStore method should return the same injected data store
        $this->assertEquals($mockStore, $instance->getDataStore());
    }
}
