<?php

namespace ProcessMaker\Nayra\Bpmn;

use DateInterval;
use DatePeriod;
use DateTime;
use Exception;
use ProcessMaker\Nayra\Bpmn\BaseTrait;
use ProcessMaker\Nayra\Contracts\Bpmn\FormalExpressionInterface;

/**
 * Formal expression base trait.
 *
 * Include timer expressions.
 *
 */
trait FormalExpressionTrait
{

    use BaseTrait;

    /**
     * Get a DateTime if the expression is a date.
     *
     * @return \DateTime
     */
    protected function getDateExpression()
    {
        $expression = $this->getProperty(FormalExpressionInterface::BPMN_PROPERTY_BODY);
        try {
            $date = new DateTime($expression);
        } catch (Exception $e) {
            $date = false;
        }
        return $date;
    }

    /**
     * Get a DatePeriod if the expression is a cycle.
     * 
     * Ex. R4/2018-05-01T00:00:00Z/PT1M
     *     R/2018-05-01T00:00:00Z/PT1M/2025-10-02T00:00:00Z
     *
     * @return \DatePeriod
     */
    protected function getCycleExpression()
    {
        $expression = $this->getProperty(FormalExpressionInterface::BPMN_PROPERTY_BODY);
        try {
            //Improve Repeating intervals (R/start/interval/end) configuration
            if (preg_match('/^R\/([^\/]+)\/([^\/]+)\/([^\/]+)$/', $expression, $repeating)) {
                $cycle = new DatePeriod(new DateTime($repeating[1]), new DateInterval($repeating[2]), new DateTime($repeating[3]));
            } else {
                $cycle = new DatePeriod($expression);
            }
        } catch (Exception $e) {
            $cycle = false;
        }
        return $cycle;
    }

    /**
     * Get a DateInterval if the expression is a duration.
     *
     * @return \DateInterval
     */
    protected function getDurationExpression()
    {
        $expression = $this->getProperty(FormalExpressionInterface::BPMN_PROPERTY_BODY);
        try {
            $duration = new DateInterval($expression);
        } catch (Exception $e) {
            $duration = false;
        }
        return $duration;
    }
}
