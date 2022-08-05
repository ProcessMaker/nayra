<?php

namespace ProcessMaker\Test\Models;

use Exception;

/**
 * Test class for evaluate expression used in betsy BPMN files.
 */
class TestBetsy
{
    public $data;

    private $code;

    /**
     * Class to test betsy processes.
     *
     * @param mixed $data
     * @param string $expression
     */
    public function __construct($data, $expression)
    {
        $this->data = $data;
        $tokens = token_get_all('<?php '.$expression);
        $tokens[0] = '';
        $code = '';
        foreach ($tokens as $token) {
            if (is_array($token) && $token[1] === 'test') {
                $code .= '$'.$token[1];
            } elseif ($token === '.') {
                $code .= '->';
            } else {
                $code .= is_array($token) ? $token[1] : $token;
            }
        }
        $this->code = $code;
    }

    /**
     * Check if the data contains and item by name.
     *
     * @param string $name
     *
     * @return bool
     */
    public function contains($name)
    {
        return isset($this->data[$name]);
    }

    /**
     * Evaluate the code.
     *
     * @return mixed
     */
    public function call()
    {
        $test = $this;
        $data = $this->data;

        return eval('return '.$this->code.';');
    }

    private function throwException($message)
    {
        throw new Exception($message);
    }
}
