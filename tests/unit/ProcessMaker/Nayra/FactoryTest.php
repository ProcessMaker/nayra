<?php

use PHPUnit\Framework\TestCase;
use ProcessMaker\Test\Models\Repository;
use ProcessMaker\Test\Contracts\TestOneInterface;
use ProcessMaker\Test\Contracts\TestTwoInterface;
use ProcessMaker\Test\Models\TestOneClassWithEmptyConstructor;
use ProcessMaker\Test\Models\TestTwoClassWithArgumentsConstructor;

class FactoryTest extends TestCase
{
    /**
     * Tests that the Factory instantiates classes whose constructors have and don't have arguments
     */
    public function testInstantiation ()
    {
        $factory = new Repository();

        //instantiate an object that implementes a TestOneInterface
        $object1 = $factory->create(TestOneInterface::class);

        //Assertion: The instantiated object is not null
        $this->assertNotNull($object1);

        //Assertion: The instantiated object implementes the TestOneInterface
        $this->assertInstanceOf(TestOneInterface::class, $object1);

        //instantiate an object that implementes a TestTwoInterface
        $object2 = $factory->create(TestTwoInterface::class, "passedField1Value", "passedField2Value");

        //Assertion: The instantiated object has uses its constructor
        $this->assertEquals($object2->aField, "passedField1Value");

        //Assertion: The instantiated object implements the TestTwoInterface
        $this->assertInstanceOf(TestTwoInterface::class, $object2);

        $this->expectException(InvalidArgumentException::class);

        //Assertion: when trying to instantiate an interface that is not mapped an argument exception should be thrown
        $object3 = $factory->create("NonExistentInterface");
    }



    /**
     * Creates factory mappings for the test
     *
     * @return array
     */
    private function createMappingConfiguration()
    {
        return [
            TestOneInterface::class => TestOneClassWithEmptyConstructor::class,
            TestTwoInterface::class => TestTwoClassWithArgumentsConstructor::class
        ];
    }
}
