<?php

namespace ProcessMaker\Nayra\Bpmn\Models;

use DatePeriod as DatePeriodBase;
use DateTime;
use DateInterval;
use DateTimeInterface;

/**
 * Application
 *
 * @package ProcessMaker\Models
 */
class DatePeriod
{
    public function __construct(...$args)
    {
        $expression = is_string($args[0]) ? $args[0] : null;
        //Improve Repeating intervals (R/start/interval/end) configuration
        if (preg_match('/^R\/([^\/]+)\/([^\/]+)\/([^\/]+)$/', $expression, $repeating)) {
            $this->start = new DateTime($repeating[1]);
            $this->interval = new DateInterval($repeating[2]);
            $this->end = new DateTime($repeating[3]);
            $this->recurrences = 0;
        //Improve Repeating intervals (R/start/interval) configuration
        } elseif (preg_match('/^R\/([^\/]+)\/([^\/]+)$/', $expression, $repeating)) {
            $this->start = new DateTime($repeating[1]);
            $this->interval = new DateInterval($repeating[2]);
            $this->end = null;
            $this->recurrences = 0;
        //Improve Repeating intervals (R/start/interval) configuration
        } elseif (preg_match('/^R(\d*)\/([^\/]+)$/', $expression, $repeating)) {
            $this->start = null;
            $this->interval = new DateInterval($repeating[2]);
            $this->end = null;
            $this->recurrences = $repeating[1] ? $repeating[1] - 1 : 0;
        } elseif (count($args) === 2 || count($args) === 3) {
            $this->start = $args[0];
            $this->interval = $args[1];
            $this->end = isset($args[2]) && $args[2] instanceof DateTimeInterface ? $args[2] : null;
            $this->recurrences = isset($args[2]) && is_int($args[2]) ? $args[2] : 0;
        } else {
            $cycle = new DatePeriodBase(...$args);
            $this->start = $cycle->start;
            $this->interval = $cycle->interval;
            $this->end = $cycle->end;
            $this->recurrences = $cycle->recurrences;
        }
    }
}
