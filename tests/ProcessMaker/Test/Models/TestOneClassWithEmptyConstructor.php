<?php

namespace ProcessMaker\Test\Models;

use ProcessMaker\Test\Contracts\TestOneInterface;

/**
 * TestOneClassWithEmptyConstructor
 */
class TestOneClassWithEmptyConstructor implements TestOneInterface
{
    public $aField;

    /**
     * Test constructor
     */
    public function __construct()
    {
        $this->aField = 'aField';
    }

    /**
     * Test function
     *
     * @return string
     */
    public function dummyFunction()
    {
        return 'test';
    }
}
