<?php

namespace ProcessMaker\Models;

use ProcessMaker\Nayra\Bpmn\BaseTrait;
use ProcessMaker\Nayra\Contracts\Bpmn\FormalExpressionInterface;

/**
 * Description of FormalExpression
 *
 */
class FormalExpression implements FormalExpressionInterface
{

    use BaseTrait;

    public function getBody()
    {
        return $this->getProperty(FormalExpressionInterface::BPMN_PROPERTY_BODY);
    }

    public function getEvaluatesToType()
    {
        return $this->getProperty(FormalExpressionInterface::BPMN_PROPERTY_EVALUATES_TO_TYPE_REF);
    }

    public function getLanguage()
    {
        return $this->getProperty(FormalExpressionInterface::BPMN_PROPERTY_LANGUAGE);
    }

    public function __invoke($data)
    {
        $sourceCode = $this->getProperty(FormalExpressionInterface::BPMN_PROPERTY_BODY);
        $tokens = token_get_all('<?php ' . $sourceCode);
        $tokens[0] = '';
        $code = '';
        $test = new TestBetsy($data);
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
