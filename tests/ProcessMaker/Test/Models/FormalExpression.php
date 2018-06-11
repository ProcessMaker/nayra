<?php

namespace ProcessMaker\Test\Models;

use DateInterval;
use DatePeriod;
use DateTime;
use Exception;
use ProcessMaker\Nayra\Bpmn\BaseTrait;
use ProcessMaker\Nayra\Contracts\Bpmn\FormalExpressionInterface;
use ProcessMaker\Test\Models\TestBetsy;

/**
 * FormalExpression implementation
 *
 */
class FormalExpression implements FormalExpressionInterface
{

    use BaseTrait;

    /**
     * Get the body of the Expression.
     *
     * @return string
     */
    public function getBody()
    {
        return $this->getProperty(FormalExpressionInterface::BPMN_PROPERTY_BODY);
    }

    /**
     * Get the type that this Expression returns when evaluated.
     *
     * @return string
     */
    public function getEvaluatesToType()
    {
        return $this->getProperty(FormalExpressionInterface::BPMN_PROPERTY_EVALUATES_TO_TYPE_REF);
    }

    /**
     * Get the expression language.
     *
     * @return string
     */
    public function getLanguage()
    {
        return $this->getProperty(FormalExpressionInterface::BPMN_PROPERTY_LANGUAGE);
    }

    /**
     * Invoke the format expression.
     *
     * @param mixed $data
     *
     * @return string
     */
    public function __invoke($data)
    {
        $expression = $this->getProperty(FormalExpressionInterface::BPMN_PROPERTY_BODY);
        if ($this->isDateExpression() || $this->isCycleExpression() || $this->isDurationExpression()) {
            return $expression;
        }
        $test = new TestBetsy($data, $expression);
        return $test->call();
    }

    /**
     * Verify if the expression is a date.
     *
     * @return boolean
     */
    private function isDateExpression()
    {
        $expression = $this->getProperty(FormalExpressionInterface::BPMN_PROPERTY_BODY);
        try {
            $date = new DateTime($expression);
        } catch (Exception $e) {
            return false;
        }
        return $date !== false;
    }

    /**
     * Verify if the expression is a cycle.
     *
     * @return boolean
     */
    private function isCycleExpression()
    {
        $expression = $this->getProperty(FormalExpressionInterface::BPMN_PROPERTY_BODY);
        try {
            $interval = new DatePeriod($expression);
        } catch (Exception $e) {
            return false;
        }
        return $interval !== false;
    }

    /**
     * Verify if the expression is a duration.
     *
     * @return boolean
     */
    private function isDurationExpression()
    {
        $expression = $this->getProperty(FormalExpressionInterface::BPMN_PROPERTY_BODY);
        try {
            $interval = new DateInterval($expression);
        } catch (Exception $e) {
            return false;
        }
        return $interval !== false;
    }
}
