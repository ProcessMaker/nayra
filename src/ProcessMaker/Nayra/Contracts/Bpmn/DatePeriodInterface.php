<?php

namespace ProcessMaker\Nayra\Contracts\Bpmn;

use DateInterval;
use DateTimeInterface;
use Iterator;

/**
 * DatePeriod represents an ISO8601 Repeating intervals
 */
interface DatePeriodInterface extends Iterator
{
    /**
     * Get start date time
     *
     * @return DateTimeInterface
     */
    public function getStartDate();

    /**
     * Get date interval
     *
     * @return DateInterval
     */
    public function getDateInterval();
}
