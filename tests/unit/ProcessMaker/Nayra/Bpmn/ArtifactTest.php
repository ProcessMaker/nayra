<?php

namespace ProcessMaker\Nayra\Bpmn;


use PHPUnit\Framework\TestCase;
use ProcessMaker\Nayra\Bpmn\Model\Process;
use ProcessMaker\Nayra\Bpmn\Models\Artifact;

class ArtifactTest extends TestCase
{
    /**
     * Tests that setters and getters are working properly
     */
    public function testSettersAndGetters()
    {
        // Create the objects that will be set
        $process = new Process();
        $artifact = new Artifact();

        //use the setters
        $artifact->setProcess($process);

        //Assertion: The set process must be equal to the created process
        $this->assertEquals($process, $artifact->getProcess());
    }
}
