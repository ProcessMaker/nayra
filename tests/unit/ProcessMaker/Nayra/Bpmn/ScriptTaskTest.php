<?php

namespace ProcessMaker\Nayra\Bpmn;


use PHPUnit\Framework\TestCase;
use ProcessMaker\Nayra\Bpmn\Model\Process;
use ProcessMaker\Nayra\Bpmn\Model\ScriptTask;

class ScriptTaskTest extends TestCase
{
    /**
     * Tests that setters and getters are working properly
     */
    public function testSettersAndGetters()
    {
        // Create the objects that will be set in the script task
        $script = new ScriptTask();
        $process = new Process();

        //set properties of the script task
        $testFormat = 'testFormat';
        $script->setCalledElement($process);
        $script->setScriptFormat($testFormat);

        //Assertion: The set called element must be equal to the created one
        $this->assertEquals($process, $script->getCalledElement());

        //Assertion: the set format must be equal to the created one
        $this->assertEquals($testFormat, $script->getScriptFormat());
    }
}