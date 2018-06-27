<?php

namespace ProcessMaker\Nayra\Bpmn;

use PHPUnit\Framework\TestCase;
use ProcessMaker\Nayra\Bpmn\Models\ScriptTask;

/**
 * Tests for the Operation class
 *
 * @package ProcessMaker\Nayra\Bpmn
 */
class ScriptTaskTest extends TestCase
{
    /**
     * Tests that setters and getters are working properly
     */
    public function testSettersAndGetters()
    {
        // Create the objects that will be set in the script task
        $script = new ScriptTask();

        //set properties of the script task
        $testFormat = 'testFormat';
        $script->setScriptFormat($testFormat);

        //Assertion: the get format must be equal to the set one
        $this->assertEquals($testFormat, $script->getScriptFormat());
    }
}
