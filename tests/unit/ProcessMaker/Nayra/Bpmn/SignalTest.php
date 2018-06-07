<?php

namespace ProcessMaker\Nayra\Bpmn;

use PHPUnit\Framework\TestCase;
use ProcessMaker\Nayra\Bpmn\Models\Signal;

/**
 * Tests for the Signal class
 *
 * @package ProcessMaker\Nayra\Bpmn
 */
class SignalTest extends TestCase
{
    /**
     * Tests that setters and getters are working properly
     */
    public function testSettersAndGetters()
    {
        // Create the objects that will be set in the data store
        $signal = new Signal();
        $testString = 'testString';

        // Use setters
        $signal->setId($testString);
        $signal->setName($testString);

        //Assertion: The getters should return the set objects
        $this->assertEquals($testString, $signal->getId());
        $this->assertEquals($testString, $signal->getName());
    }
}
