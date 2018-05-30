<?php

use PHPUnit\Framework\TestCase;
use ProcessMaker\Nayra\Factory;

class FactoryTest extends TestCase
{
    /**
     * Tests that the Factory instantiates classes whose constructors have and don't have arguments
     */
    public function testInstantiation ()
    {
        //create a mapping for the factory
        $config = $this->createMappingConfiguration();
        $factory = new Factory($config);

        //instantiate an object that implementes a DummyInterface1
        $object1 = $factory->createInstanceOf(DummyInterface1::class);

        //Assertion: The instantiated object is not null
        $this->assertNotNull($object1);

        //Assertion: The instantiated object implementes the DummyInterface1
        $this->assertInstanceOf(DummyInterface1::class, $object1);

        //instantiate an object that implementes a DummyInterface2
        $object2 = $factory->createInstanceOf(DummyInterface2 ::class, "passedField1Value", "passedField2Value");

        //Assertion: The instantiated object has uses its constructor
        $this->assertEquals($object2->aField, "passedField1Value");

        //Assertion: The instantiated object implements the DummyInterface2
        $this->assertInstanceOf(DummyInterface2::class, $object2);

        $this->expectException(InvalidArgumentException::class);

        //Assertion: when trying to instantiate an interface that is not mapped an argument exception should be thrown
        $object3 = $factory->createInstanceOf("NonExistentInterface");
    }



    /**
     * Creates factory mappings for the test
     *
     * @return array
     */
    private function createMappingConfiguration()
    {
        return [
            DummyInterface1::class => DummyClassWithEmptyConstructor::class,
            DummyInterface2::class => DummyClassWithArgumentsConstructor::class
        ];
    }
}

/**
 * Interface to be used in the test
 */
interface DummyInterface1
{
   public function dummyFunction();
}

/**
 * Interface to be used in the test
 */
interface DummyInterface2
{
    public function dummyFunction();
}

/**
 * Class with constructor without parameters to be used in the test
 */
class DummyClassWithEmptyConstructor implements DummyInterface1
{
    public $aField;

    public function __construct()
    {
        $this->aField = 'aField';
    }

    public function dummyFunction()
    {
        return "test";
    }
}

/**
 * Class with arguments in the constructor to be used in the test
 */
class DummyClassWithArgumentsConstructor implements DummyInterface2
{
    public $aField;
    public $anotherField;

    public function __construct($field1, $field2)
    {
        $this->aField = $field1;
        $this->anotherField = $field2;
    }

    public function dummyFunction()
    {
        return "test";
    }
}

