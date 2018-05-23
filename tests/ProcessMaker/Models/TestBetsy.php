<?php

namespace ProcessMaker\Models;

/**
 * Test class for evaluate expression used in betsy BPMN files.
 *
 */
class TestBetsy
{
    public $data;
    private $code;

    public function __construct($data, $expression)
    {
        $this->data = $data;
        $tokens = token_get_all('<?php ' . $expression);
        $tokens[0] = '';
        $code = '';
        foreach ($tokens as $token) {
            if (is_array($token) && $token[1] === 'test') {
                $code .= '$' . $token[1];
            } elseif ($token === '.') {
                $code .= '->';
            } else {
                $code .= is_array($token) ? $token[1] : $token;
            }
        }
        $this->code = $code;
    }

    public function contains($name)
    {
        return isset($this->data[$name]);
    }

    public function call()
    {
        $test = $this;
        $data = $this->data;
        return eval('return ' . $this->code . ';');
    }
}
