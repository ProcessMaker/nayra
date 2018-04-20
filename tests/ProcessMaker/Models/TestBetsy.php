<?php

namespace ProcessMaker\Models;

/**
 * Test class for evaluate expression used in betsy BPMN files.
 *
 */
class TestBetsy
{
    public $data;

    public function __construct($data)
    {
        $this->data = $data;
    }

    public function contains($name)
    {
        return isset($this->data[$name]);
    }
}
