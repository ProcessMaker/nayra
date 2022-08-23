<?php

namespace ProcessMaker\Nayra\Bpmn\Models;

use DateInterval;
use DateTime;
use DateTimeInterface;
use Exception;
use Iterator;
use ProcessMaker\Nayra\Contracts\Bpmn\DatePeriodInterface;

/**
 * DatePeriod represents an ISO8601 Repeating intervals
 */
class DatePeriod implements DatePeriodInterface
{
    /**
     * Start date of the period
     *
     * @var DateTime
     */
    public $start;

    public $current;

    /**
     * End date of the period
     *
     * @var DateTime
     */
    public $end;

    /**
     * Interval of the period
     *
     * @var DateInterval
     */
    public $interval;

    /**
     * Number of recurrences of the period
     *
     * @var int
     */
    public $recurrences;

    public $include_start_date = true;

    private $position = 0;

    private $last;

    const INF_RECURRENCES = 0;

    const EXCLUDE_START_DATE = 1;

    /**
     * Initialize a DatePeriod.
     *
     * Parameters could be:
     * - ISO8601 Repeating intervals  R[n]/start/interval/end
     * - start, interval, [end|array(end,recurences-1)]
     */
    public function __construct(...$args)
    {
        $expression = is_string($args[0]) ? $args[0] : null;
        //Improve Repeating intervals (R/start/interval/end) configuration
        if (preg_match('/^R(\d*)\/([^\/]+)\/([^\/]+)\/([^\/]+)$/', $expression, $repeating)) {
            $this->start = new DateTime($repeating[2]);
            $this->interval = new DateInterval($repeating[3]);
            $this->end = new DateTime($repeating[4]);
            $this->recurrences = $repeating[1] ? $repeating[1] + 1 : self::INF_RECURRENCES;
        //Improve Repeating intervals (R[n]/start/interval) or (R[n]/interval/end) configuration
        } elseif (preg_match('/^R(\d*)\/([^\/]+)\/([^\/]+)$/', $expression, $repeating)) {
            $withoutStart = substr($repeating[2], 0, 1) === 'P';
            $this->start = $withoutStart ? null : new DateTime($repeating[2]);
            $this->interval = new DateInterval($repeating[$withoutStart ? 2 : 3]);
            $this->end = $withoutStart ? new DateTime($repeating[3]) : null;
            $this->recurrences = $repeating[1] ? $repeating[1] + 1 : self::INF_RECURRENCES;
        //Improve Repeating intervals (R[n]/start/interval) configuration
        } elseif (preg_match('/^R(\d*)\/([^\/]+)$/', $expression, $repeating)) {
            $this->start = null;
            $this->interval = new DateInterval($repeating[2]);
            $this->end = null;
            $this->recurrences = $repeating[1] ? $repeating[1] + 1 : self::INF_RECURRENCES;
        } elseif (count($args) === 3 && is_array($args[2])) {
            $this->start = $args[0];
            $this->interval = $args[1];
            $this->end = $args[2][0];
            $this->recurrences = $args[2][1] + 1;
        } elseif (count($args) === 2 || count($args) === 3) {
            $this->start = $args[0];
            $this->interval = $args[1];
            $this->end = isset($args[2]) && $args[2] instanceof DateTimeInterface ? $args[2] : null;
            $this->recurrences = isset($args[2]) && is_int($args[2]) ? $args[2] + 1 : self::INF_RECURRENCES;
        } else {
            throw new Exception('Invalid DatePeriod definition');
        }
        // Validate properties
        if (isset($this->start) && !($this->start instanceof DateTimeInterface)) {
            throw new Exception('Invalid DatePeriod::start definition');
        }
        if (!($this->interval instanceof DateInterval)) {
            throw new Exception('Invalid DatePeriod::interval definition');
        }
        if (isset($this->end) && !($this->end instanceof DateTimeInterface)) {
            throw new Exception('Invalid DatePeriod::end definition');
        }
        if (!($this->recurrences >= 0)) {
            throw new Exception('Invalid DatePeriod::recurrences definition');
        }
    }

    /**
     * Get start date time
     *
     * @return DateTimeInterface
     */
    public function getStartDate()
    {
        return $this->start;
    }

    /**
     * Get date interval
     *
     * @return DateInterval
     */
    public function getDateInterval()
    {
        return $this->interval;
    }

    /**
     * Get current datetime for an iteration
     *
     * @return DateTimeInterface
     */
    public function current()
    {
        return $this->current;
    }

    /**
     * Get current position for an iteration
     *
     * @return int
     */
    public function key()
    {
        return $this->position;
    }

    /**
     * Iterate to the next datetime
     */
    public function next()
    {
        $this->position++;
        $this->current = $this->calc($this->current, 1);
    }

    /**
     * Rewind iteration to the first datetime
     */
    public function rewind()
    {
        $this->current = $this->start ?: ($this->end ? $this->calc($this->end, -$this->recurrences) : new DateTime());
        $this->last = $this->end ?: $this->calc($this->current, $this->recurrences);
        $this->position = 0;
    }

    /**
     * Check if iteration continues
     *
     * @return bool
     */
    public function valid()
    {
        return $this->current <= $this->last;
    }

    /**
     * Calculate next/previos date
     *
     * @param DateTimeInterface $date
     * @param int $count
     *
     * @return DateTimeInterface
     */
    private function calc(DateTimeInterface $date, $count)
    {
        $date = clone $date;
        while ($count) {
            $count <= 0 ?: $date->add($this->interval);
            $count >= 0 ?: $date->sub($this->interval);
            $count > 0 ? $count-- : $count++;
        }

        return $date;
    }
}
