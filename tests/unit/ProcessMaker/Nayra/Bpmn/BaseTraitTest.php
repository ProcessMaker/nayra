<?php

namespace ProcessMaker\Nayra\Bpmn;

use PHPUnit\Framework\TestCase;
use ProcessMaker\Nayra\Bpmn\Models\Process;
use ProcessMaker\Nayra\Contracts\Bpmn\EntityInterface;

/**
 * Test the base trait behavior.
 *
 */
class BaseTraitTest extends TestCase
{

    /**
     * Test ID property getter and setter
     *
     */
    public function testSetGetId()
    {
        $testId = 'testId';

        $process = new Process();

        $originalNumberOfProperties = count($process->getProperties());

        $process->setId($testId);

        $this->assertEquals(
            $testId,
            $process->getId(),
            'The stored id must be equal to the testId'
        );

        $this->assertCount(
            $originalNumberOfProperties + 1,
            $process->getProperties(),
            'The properties array must have one item'
        );

        $this->assertEquals(
            $testId,
            $process->getProperties()[EntityInterface::BPMN_PROPERTY_ID],
            'The properties array must have one item (the id)'
        );
    }
}
