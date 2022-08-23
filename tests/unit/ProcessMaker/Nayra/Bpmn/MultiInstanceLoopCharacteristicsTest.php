<?php

namespace ProcessMaker\Nayra\Bpmn;

use PHPUnit\Framework\TestCase;
use ProcessMaker\Nayra\Bpmn\Models\DataInput;
use ProcessMaker\Nayra\Bpmn\Models\DataOutput;
use ProcessMaker\Nayra\Bpmn\Models\MultiInstanceLoopCharacteristics;
use ProcessMaker\Nayra\Contracts\Bpmn\ComplexBehaviorDefinitionInterface;
use ProcessMaker\Test\Models\FormalExpression;

/**
 * Tests MultiInstanceLoopCharacteristics class getter/setters
 */
class MultiInstanceLoopCharacteristicsTest extends TestCase
{
    /**
     * Tests that setters and getters are working properly
     */
    public function testSettersAndGetters()
    {
        // Create the objects that will be set in the data store
        $object = new MultiInstanceLoopCharacteristics();

        $isSequential = true;
        $object->setIsSequential($isSequential);
        $this->assertEquals($isSequential, $object->getIsSequential());

        $behavior = 'All';
        $object->setBehavior($behavior);
        $this->assertEquals($behavior, $object->getBehavior());

        $oneBehaviorEventRef = 'event_1';
        $object->setOneBehaviorEventRef($oneBehaviorEventRef);
        $this->assertEquals($oneBehaviorEventRef, $object->getOneBehaviorEventRef());

        $noneBehaviorEventRef = 'event_2';
        $object->setNoneBehaviorEventRef($noneBehaviorEventRef);
        $this->assertEquals($noneBehaviorEventRef, $object->getNoneBehaviorEventRef());

        $loopCardinality = new FormalExpression('3');
        $object->setLoopCardinality($loopCardinality);
        $this->assertEquals($loopCardinality, $object->getLoopCardinality());

        $loopDataInputRef = 'data_input_1';
        $object->setLoopDataInputRef($loopDataInputRef);
        $this->assertEquals($loopDataInputRef, $object->getLoopDataInputRef());

        $loopDataInput = new DataInput();
        $object->setLoopDataInput($loopDataInput);
        $this->assertEquals($loopDataInput, $object->getLoopDataInput());

        $loopDataOutputRef = 'data_output_1';
        $object->setLoopDataOutputRef($loopDataOutputRef);
        $this->assertEquals($loopDataOutputRef, $object->getLoopDataOutputRef());

        $loopDataOutput = new DataOutput();
        $object->setLoopDataOutput($loopDataOutput);
        $this->assertEquals($loopDataOutput, $object->getLoopDataOutput());

        $inputDataItem = new DataInput();
        $object->setInputDataItem($inputDataItem);
        $this->assertEquals($inputDataItem, $object->getInputDataItem());

        $outputDataItem = new DataOutput();
        $object->setOutputDataItem($outputDataItem);
        $this->assertEquals($outputDataItem, $object->getOutputDataItem());

        $complexBehaviorDefinition = $this->createMock(ComplexBehaviorDefinitionInterface::class);
        $object->setComplexBehaviorDefinition($complexBehaviorDefinition);
        $this->assertEquals($complexBehaviorDefinition, $object->getComplexBehaviorDefinition());

        $completionCondition = new FormalExpression('true');
        $object->setCompletionCondition($completionCondition);
        $this->assertEquals($completionCondition, $object->getCompletionCondition());
    }
}
