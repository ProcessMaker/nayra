<?php

namespace ProcessMaker\Nayra\Bpmn;

use PHPUnit\Framework\TestCase;
use ProcessMaker\Nayra\Bpmn\Models\InputOutputSpecification;
use ProcessMaker\Nayra\Bpmn\Models\InputSet;
use ProcessMaker\Nayra\Bpmn\Models\OutputSet;

/**
 * Tests InputOutputSpecification class implementation
 */
class InputOutputSpecificationTest extends TestCase
{
    /**
     * Tests that setters and getters are working properly
     */
    public function testSettersAndGetters()
    {
        // Create the objects that will be set in the data store
        $ioSpecification = new InputOutputSpecification();

        $dataOutput = new Collection();
        $ioSpecification->setDataOutput($dataOutput);
        $this->assertEquals($dataOutput, $ioSpecification->getDataOutput());

        $dataInput = new Collection();
        $ioSpecification->setDataInput($dataInput);
        $this->assertEquals($dataInput, $ioSpecification->getDataInput());

        $outputSet = new OutputSet();
        $ioSpecification->setOutputSet($outputSet);
        $this->assertEquals($outputSet, $ioSpecification->getOutputSet());

        $inputSet = new InputSet();
        $ioSpecification->setInputSet($inputSet);
        $this->assertEquals($inputSet, $ioSpecification->getInputSet());
    }
}
