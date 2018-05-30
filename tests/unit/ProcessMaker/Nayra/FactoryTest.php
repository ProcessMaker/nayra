<?php

use PHPUnit\Framework\TestCase;
use ProcessMaker\Nayra\Contracts\Factory;

class FactoryTest extends TestCase
{
    /**
     * Tests that the Factory instantiates classes whose constructors have and don't have arguments
     */
    public function testInstantiation ()
    {
        $config = $this->createMappingConfiguration();
        $factory = new Factory($config);

        $object1 = $factory->getInstanceOf(DummyInterface1::class);
        $this->assertNotNull($object1);

        $object2 = $factory->getInstanceOf(DummyInterface2 ::class, "passedField1Value", "passedField2Value");
        $this->assertEquals($object2->aField, "passedField1Value");

        $this->expectException(InvalidArgumentException::class);
        $object2 = $factory->getInstanceOf("NonExistentInterface");
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

