<?php

namespace ProcessMaker\Test\Models;

use ProcessMaker\Test\Contracts\TestTwoInterface;

/**
 * TestOneClassWithEmptyConstructor
 *
 * @package ProcessMaker\Test\Models
 */
class TestTwoClassWithArgumentsConstructor implements TestTwoInterface
{
    public $aField;
    public $anotherField;

    /**
     * Test constructor
     *
     * @param mixed $field1
     * @param mixed $field2
     */
    public function __construct($field1, $field2)
    {
        $this->aField = $field1;
        $this->anotherField = $field2;
    }

    /**
     * Test function
     *
     * @return string
     */
    public function dummyFunction()
    {
        return "test";
    }
}
