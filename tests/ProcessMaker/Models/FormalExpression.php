<?php

namespace ProcessMaker\Models;

use ProcessMaker\Nayra\Bpmn\BaseTrait;

/**
 * Description of FormalExpression
 *
 */
class FormalExpression implements \ProcessMaker\Nayra\Contracts\Bpmn\FormalExpressionInterface
{

    use BaseTrait;

    public function getBody()
    {
        return $this->getProperty('body');
    }

    public function getEvaluatesToType()
    {
        return $this->getProperty('evaluatesToTypeRef');
    }

    public function getLanguage()
    {
        return $this->getProperty('language');
    }

    public function __invoke($data)
    {
        $tokens = token_get_all('<?php ' . $this->getProperty('body'));
        $tokens[0] = '';
        $code = '';
        $test = new Test($data);
        foreach ($tokens as $token) {
            if (is_array($token) && $token[1] === 'test') {
                $code .= '$' . $token[1];
            } elseif ($token === '.') {
                $code .= '->';
            } else {
                $code .= is_array($token) ? $token[1] : $token;
            }
        }
        return eval('return ' . $code . ';');
    }
}

class Test
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
